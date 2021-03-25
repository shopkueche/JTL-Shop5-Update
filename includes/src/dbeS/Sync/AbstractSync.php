<?php

namespace JTL\dbeS\Sync;

use DateTime;
use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\Campaign;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\dbeS\Mapper;
use JTL\dbeS\Starter;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Optin\Optin;
use JTL\Optin\OptinAvailAgain;
use JTL\Redirect;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class AbstractSync
 * @package JTL\dbeS\Sync
 */
abstract class AbstractSync
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * Products constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param LoggerInterface   $logger
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->cache  = $cache;
        $this->logger = $logger;
        $this->mapper = new Mapper();
    }

    /**
     * @param Starter $starter
     * @return mixed|null
     */
    abstract public function handle(Starter $starter);

    /**
     * @param array      $xml
     * @param string     $table
     * @param string     $toMap
     * @param string     $pk1
     * @param int|string $pk2
     */
    protected function upsertXML($xml, $table, $toMap, $pk1, $pk2 = 0): void
    {
        $idx = $table . ' attr';
        if (GeneralObject::isCountable($table, $xml) || GeneralObject::isCountable($idx, $xml)) {
            $this->upsert($table, $this->mapper->mapArray($xml, $table, $toMap), $pk1, $pk2);
        }
    }

    /**
     * @param array  $xml
     * @param string $table
     * @param string $toMap
     * @param array  $pks
     * @return array
     */
    protected function insertOnExistsUpdateXMLinDB(array $xml, string $table, string $toMap, array $pks): array
    {
        $idx = $table . ' attr';
        if (GeneralObject::isCountable($table, $xml) || GeneralObject::isCountable($idx, $xml)) {
            return $this->insertOnExistUpdate($table, $this->mapper->mapArray($xml, $table, $toMap), $pks);
        }

        return \array_fill_keys($pks, []);
    }

    /**
     * @param string     $tablename
     * @param array      $objects
     * @param string     $pk1
     * @param string|int $pk2
     */
    protected function upsert($tablename, array $objects, $pk1, $pk2 = 0): void
    {
        foreach ($objects as $object) {
            if (isset($object->$pk1) && !$pk2 && $pk1 && $object->$pk1) {
                $this->db->delete($tablename, $pk1, $object->$pk1);
            }
            if (isset($object->$pk2) && $pk1 && $pk2 && $object->$pk1 && $object->$pk2) {
                $this->db->delete($tablename, [$pk1, $pk2], [$object->$pk1, $object->$pk2]);
            }
            $key = $this->db->insert($tablename, $object);
            if (!$key) {
                $this->logger->error('Failed upsert@' . $tablename . ' with data: ' . \print_r($object, true));
            }
        }
    }

    /**
     * @param string $tableName
     * @param array  $objects
     * @param array  $pks
     * @return array
     */
    protected function insertOnExistUpdate(string $tableName, array $objects, array $pks): array
    {
        $result = \array_fill_keys($pks, []);
        if (!\is_array($objects)) {
            return $result;
        }
        if (!\is_array($pks)) {
            $pks = [(string)$pks];
        }

        foreach ($objects as $object) {
            foreach ($pks as $pk) {
                if (!isset($object->$pk)) {
                    $this->logger->error(
                        'PK not set on insertOnExistUpdate@' . $tableName . ' with data: ' . \print_r($object, true)
                    );

                    continue 2;
                }
                $result[$pk][] = $object->$pk;
            }

            if ($this->db->upsert($tableName, $object, $pks)) {
                $this->logger->error(
                    'Failed insertOnExistUpdate@' . $tableName . ' with data: ' . \print_r($object, true)
                );
            }
        }

        return $result;
    }

    /**
     * @param string $tableName
     * @param array  $pks
     * @param string $excludeKey
     * @param array  $excludeValues
     * @return void
     */
    protected function deleteByKey(
        string $tableName,
        array $pks,
        string $excludeKey = '',
        array $excludeValues = []
    ): void {
        $whereKeys = [];
        $params    = [];
        foreach ($pks as $name => $value) {
            $whereKeys[]   = $name . ' = :' . $name;
            $params[$name] = $value;
        }
        if (empty($excludeKey) || !\is_array($excludeValues)) {
            $excludeValues = [];
        }
        $stmt = 'DELETE FROM ' . $tableName . '
                WHERE ' . \implode(' AND ', $whereKeys) . (\count($excludeValues) > 0 ? '
                    AND ' . $excludeKey . ' NOT IN (' . \implode(', ', $excludeValues) . ')' : '');
        if (!$this->db->queryPrepared($stmt, $params, ReturnType::DEFAULT)) {
            $this->logger->error(
                'DBDeleteByKey fehlgeschlagen! Tabelle: ' . $tableName . ', PK: ' . \print_r($pks, true)
            );
        }
    }

    /**
     * @param object $product
     * @param array  $conf
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @throws EmptyResultSetException
     */
    protected function sendAvailabilityMails($product, array $conf): void
    {
        if ($product->kArtikel <= 0) {
            return;
        }
        $subscriptions = $this->db->selectAll(
            'tverfuegbarkeitsbenachrichtigung',
            ['nStatus', 'kArtikel'],
            [0, $product->kArtikel]
        );
        $subCount      = \count($subscriptions);
        if ($subCount === 0
            || (($product->fLagerbestand / $subCount) < ($conf['artikeldetails']['benachrichtigung_min_lagernd'] / 100)
                && ($product->cLagerKleinerNull ?? '') !== 'Y'
                && (!isset($product->cLagerBeachten)
                    || $product->cLagerBeachten === 'Y')
            )
        ) {
            return;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';

        $options                             = Artikel::getDefaultOptions();
        $options->nKeineSichtbarkeitBeachten = 1;
        $product                             = (new Artikel())->fuelleArtikel($product->kArtikel, $options);
        if ($product === null) {
            return;
        }
        $campaign = new Campaign(\KAMPAGNE_INTERN_VERFUEGBARKEIT);
        if ($campaign->kKampagne > 0) {
            $sep            = \strpos($product->cURL, '.php') === false ? '?' : '&';
            $product->cURL .= $sep . $campaign->cParameter . '=' . $campaign->cWert;
        }
        foreach ($subscriptions as $msg) {
            $availAgainOptin = (new Optin(OptinAvailAgain::class))->setEmail($msg->cMail);
            if (!$availAgainOptin->isActive()) {
                continue;
            }
            $availAgainOptin->finishOptin();
            $tplData                                   = new stdClass();
            $tplData->tverfuegbarkeitsbenachrichtigung = $msg;
            $tplData->tartikel                         = $product;
            $tplData->tartikel->cName                  = Text::htmlentitydecode($tplData->tartikel->cName);
            $tplMail                                   = new stdClass();
            $tplMail->toEmail                          = $msg->cMail;
            $tplMail->toName                           = ($msg->cVorname || $msg->cNachname)
                ? ($msg->cVorname . ' ' . $msg->cNachname)
                : $msg->cMail;
            $tplData->mail                             = $tplMail;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mail->setToMail($tplMail->toEmail);
            $mail->setToName($tplMail->toName);
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $tplData));

            $upd                    = new stdClass();
            $upd->nStatus           = 1;
            $upd->dBenachrichtigtAm = 'NOW()';
            $upd->cAbgeholt         = 'N';
            $this->db->update(
                'tverfuegbarkeitsbenachrichtigung',
                'kVerfuegbarkeitsbenachrichtigung',
                $msg->kVerfuegbarkeitsbenachrichtigung,
                $upd
            );
        }
    }

    /**
     * @param int   $productID
     * @param array $xml
     * @throws Exception
     */
    protected function handlePriceHistory(int $productID, array $xml): void
    {
        if (!\is_array($xml)) {
            return;
        }
        // Delete price history from not existing customer groups
        $this->db->queryPrepared(
            'DELETE tpreisverlauf
                FROM tpreisverlauf
                    LEFT JOIN tkundengruppe ON tkundengruppe.kKundengruppe = tpreisverlauf.kKundengruppe
                WHERE tpreisverlauf.kArtikel = :productID
                    AND tkundengruppe.kKundengruppe IS NULL',
            ['productID' => $productID],
            ReturnType::DEFAULT
        );
        // Insert new base price for each customer group - update existing history for today
        $this->db->queryPrepared(
            'INSERT INTO tpreisverlauf (kArtikel, kKundengruppe, fVKNetto, dDate)
                SELECT :productID, kKundengruppe, :nettoPrice, CURDATE()
                FROM tkundengruppe
                ON DUPLICATE KEY UPDATE
                    fVKNetto = :nettoPrice',
            [
                'productID'  => $productID,
                'nettoPrice' => (float)$xml['fStandardpreisNetto'],
            ],
            ReturnType::DEFAULT
        );
        // Handle price details from xml...
        $this->handlePriceDetails($productID, $xml);
        // Handle special prices from xml...
        $this->handleSpecialPrices($productID, $xml);
        // Delete last price history if price is same as next to last
        $this->db->queryPrepared(
            'DELETE FROM tpreisverlauf
                WHERE tpreisverlauf.kArtikel = :productID
                    AND (tpreisverlauf.kKundengruppe, tpreisverlauf.dDate) IN (SELECT * FROM (
                        SELECT tpv1.kKundengruppe, MAX(tpv1.dDate)
                        FROM tpreisverlauf tpv1
                        LEFT JOIN tpreisverlauf tpv2 ON tpv2.dDate > tpv1.dDate
                            AND tpv2.kArtikel = tpv1.kArtikel
                            AND tpv2.kKundengruppe = tpv1.kKundengruppe
                            AND tpv2.dDate < (
                                SELECT MAX(tpv3.dDate)
                                FROM tpreisverlauf tpv3
                                WHERE tpv3.kArtikel = tpv1.kArtikel
                                    AND tpv3.kKundengruppe = tpv1.kKundengruppe
                            )
                        WHERE tpv1.kArtikel = :productID
                            AND tpv2.kPreisverlauf IS NULL
                        GROUP BY tpv1.kKundengruppe
                        HAVING COUNT(DISTINCT tpv1.fVKNetto) = 1
                            AND COUNT(tpv1.kPreisverlauf) > 1
                    ) i)',
            ['productID' => $productID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int   $productID
     * @param array $xml
     */
    private function handlePriceDetails(int $productID, array $xml): void
    {
        $prices = isset($xml['tpreis']) ? $this->mapper->mapArray($xml, 'tpreis', 'mPreis') : [];
        foreach ($prices as $i => $price) {
            $details = empty($xml['tpreis'][$i])
                ? $this->mapper->mapArray($xml['tpreis'], 'tpreisdetail', 'mPreisDetail')
                : $this->mapper->mapArray($xml['tpreis'][$i], 'tpreisdetail', 'mPreisDetail');
            if (\count($details) > 0 && (int)$details[0]->nAnzahlAb === 0) {
                $this->db->queryPrepared(
                    'UPDATE tpreisverlauf SET
                        fVKNetto = :nettoPrice
                        WHERE kArtikel = :productID
                            AND kKundengruppe = :customerGroupID
                            AND dDate = CURDATE()',
                    [
                        'nettoPrice'      => $details[0]->fNettoPreis,
                        'productID'       => $productID,
                        'customerGroupID' => $price->kKundenGruppe,
                    ],
                    ReturnType::DEFAULT
                );
            }
        }
    }

    /**
     * @param int   $productID
     * @param array $xml
     * @throws Exception
     */
    private function handleSpecialPrices(int $productID, array $xml): void
    {
        $prices = isset($xml['tartikelsonderpreis'])
            ? $this->mapper->mapArray($xml, 'tartikelsonderpreis', 'mArtikelSonderpreis')
            : [];
        foreach ($prices as $i => $price) {
            if ($price->cAktiv !== 'Y') {
                continue;
            }
            try {
                $startDate = new DateTime($price->dStart);
            } catch (Exception $e) {
                $startDate = (new DateTime())->setTime(0, 0);
            }
            try {
                $endDate = new DateTime($price->dEnde);
            } catch (Exception $e) {
                $endDate = (new DateTime())->setTime(0, 0);
            }
            $today = (new DateTime())->setTime(0, 0);
            if ($startDate <= $today
                && $endDate >= $today
                && ((int)$price->nIstAnzahl === 0 || (int)$price->nAnzahl < (int)$xml['fLagerbestand'])
            ) {
                $specialPrices = empty($xml['tartikelsonderpreis'][$i])
                    ? $this->mapper->mapArray($xml['tartikelsonderpreis'], 'tsonderpreise', 'mSonderpreise')
                    : $this->mapper->mapArray($xml['tartikelsonderpreis'][$i], 'tsonderpreise', 'mSonderpreise');

                foreach ($specialPrices as $specialPrice) {
                    $this->db->queryPrepared(
                        'UPDATE tpreisverlauf SET
                            fVKNetto = :nettoPrice
                            WHERE kArtikel = :productID
                                AND kKundengruppe = :customerGroupID
                                AND dDate = CURDATE()',
                        [
                            'nettoPrice'      => $specialPrice->fNettoPreis,
                            'productID'       => $productID,
                            'customerGroupID' => $specialPrice->kKundengruppe,
                        ],
                        ReturnType::DEFAULT
                    );
                }
            }
        }
    }


    /**
     * @param int $productID
     * @param int $customerGroupID
     * @param int $customerID
     * @return mixed
     */
    protected function handlePriceFormat(int $productID, int $customerGroupID, int $customerID = 0)
    {
        if ($customerID > 0) {
            $this->flushCustomerPriceCache($customerID);
        }

        return $this->db->queryPrepared(
            'INSERT INTO tpreis (kArtikel, kKundengruppe, kKunde)
                VALUES (:productID, :customerGroup, :customerID)
                ON DUPLICATE KEY UPDATE
                    kKunde = :customerID',
            [
                'productID'     => $productID,
                'customerGroup' => $customerGroupID,
                'customerID'    => $customerID,
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * Handle new PriceFormat (Wawi >= v.1.00):
     *
     * Sample XML:
     *  <tpreis kPreis="8" kArtikel="15678" kKundenGruppe="1" kKunde="0">
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>100</nAnzahlAb>
     *          <fNettoPreis>0.756303</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>250</nAnzahlAb>
     *          <fNettoPreis>0.714286</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>500</nAnzahlAb>
     *          <fNettoPreis>0.672269</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>750</nAnzahlAb>
     *          <fNettoPreis>0.630252</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>1000</nAnzahlAb>
     *          <fNettoPreis>0.588235</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>2000</nAnzahlAb>
     *          <fNettoPreis>0.420168</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>0</nAnzahlAb>
     *          <fNettoPreis>0.798319</fNettoPreis>
     *      </tpreisdetail>
     *  </tpreis>
     *
     * @param int   $productID
     * @param array $xml
     */
    protected function handleNewPriceFormat(int $productID, array $xml): void
    {
        if (!\is_array($xml)) {
            return;
        }

        $prices = isset($xml['tpreis']) ? $this->mapper->mapArray($xml, 'tpreis', 'mPreis') : [];
        // Delete prices and price details from not existing customer groups
        $this->db->queryPrepared(
            'DELETE tpreis, tpreisdetail
                FROM tpreis
                    INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                    LEFT JOIN tkundengruppe ON tkundengruppe.kKundengruppe = tpreis.kKundengruppe
                WHERE tpreis.kArtikel = :productID
                    AND tkundengruppe.kKundengruppe IS NULL',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Delete all prices who are not base prices
        $this->db->queryPrepared(
            'DELETE tpreisdetail
                FROM tpreis
                    INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                WHERE tpreis.kArtikel = :productID
                    AND tpreisdetail.nAnzahlAb > 0',
            ['productID' => $productID],
            ReturnType::DEFAULT
        );
        // Insert price record for each customer group - ignore existing
        $this->db->queryPrepared(
            'INSERT IGNORE INTO tpreis (kArtikel, kKundengruppe, kKunde)
                SELECT :productID, kKundengruppe, 0
                FROM tkundengruppe',
            ['productID' => $productID],
            ReturnType::DEFAULT
        );
        // Insert base price for each price record - update existing
        $this->db->queryPrepared(
            'INSERT INTO tpreisdetail (kPreis, nAnzahlAb, fVKNetto)
                SELECT tpreis.kPreis, 0, :basePrice
                FROM tpreis
                WHERE tpreis.kArtikel = :productID
                ON DUPLICATE KEY UPDATE
                    tpreisdetail.fVKNetto = :basePrice',
            [
                'basePrice' => $xml['fStandardpreisNetto'],
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Handle price details from xml...
        foreach ($prices as $i => $price) {
            $price->kKunde        = (int)($price->kKunde ?? 0);
            $price->kKundenGruppe = (int)($price->kKundenGruppe ?? 0);
            $this->handlePriceFormat((int)$price->kArtikel, $price->kKundenGruppe, $price->kKunde);
            $details = empty($xml['tpreis'][$i])
                ? $this->mapper->mapArray($xml['tpreis'], 'tpreisdetail', 'mPreisDetail')
                : $this->mapper->mapArray($xml['tpreis'][$i], 'tpreisdetail', 'mPreisDetail');

            foreach ($details as $preisdetail) {
                $this->db->queryPrepared(
                    'INSERT INTO tpreisdetail (kPreis, nAnzahlAb, fVKNetto)
                        SELECT tpreis.kPreis, :countingFrom, :nettoPrice
                        FROM tpreis
                        WHERE tpreis.kArtikel = :productID
                            AND tpreis.kKundengruppe = :customerGroup
                            AND tpreis.kKunde = :customerPrice
                        ON DUPLICATE KEY UPDATE
                            tpreisdetail.fVKNetto = :nettoPrice',
                    [
                        'countingFrom'  => $preisdetail->nAnzahlAb,
                        'nettoPrice'    => $preisdetail->fNettoPreis,
                        'productID'     => $productID,
                        'customerGroup' => $price->kKundenGruppe,
                        'customerPrice' => $price->kKunde,
                    ],
                    ReturnType::DEFAULT
                );
            }
        }
    }

    /**
     * @param int $customerID
     * @return bool|int
     */
    protected function flushCustomerPriceCache(int $customerID)
    {
        return $this->cache->flush('custprice_' . $customerID);
    }

    /**
     * @param string $salutation
     * @return string
     */
    protected function mapSalutation(string $salutation): string
    {
        $salutation = \strtolower($salutation);
        if ($salutation === 'w' || $salutation === 'm') {
            return $salutation;
        }
        if ($salutation === 'frau' || $salutation === 'mrs' || $salutation === 'mrs.') {
            return 'w';
        }

        return 'm';
    }

    /**
     * @param int         $keyValue
     * @param string      $keyName
     * @param int|null    $langID
     * @param string|null $assoc
     * @return array|null|stdClass
     */
    protected function getSeoFromDB(int $keyValue, string $keyName, int $langID = null, $assoc = null)
    {
        if ($keyValue <= 0 || $keyName === '') {
            return null;
        }
        if ($langID > 0) {
            $seo = $this->db->select('tseo', 'kKey', $keyValue, 'cKey', $keyName, 'kSprache', $langID);

            return isset($seo->kKey) && (int)$seo->kKey > 0 ? $seo : null;
        }
        $seo = $this->db->selectAll('tseo', ['kKey', 'cKey'], [$keyValue, $keyName]);
        if (\count($seo) === 0) {
            return null;
        }
        if ($assoc !== null && $assoc !== '') {
            $seoData = [];
            foreach ($seo as $oSeo) {
                if (isset($oSeo->{$assoc})) {
                    $seoData[$oSeo->{$assoc}] = $oSeo;
                }
            }
            if (\count($seoData) > 0) {
                $seo = $seoData;
            }
        }

        return $seo;
    }

    /**
     * @param array $arr
     * @param array $excludes
     * @return array
     */
    protected function buildAttributes(&$arr, $excludes = []): array
    {
        $attributes = [];
        if (!\is_array($arr)) {
            return $attributes;
        }
        foreach (\array_keys($arr) as $key) {
            if (!\in_array($key, $excludes, true) && $key[0] === 'k') {
                $attributes[$key] = $arr[$key];
                unset($arr[$key]);
            }
        }

        return $attributes;
    }

    /**
     * @param object $object
     */
    protected function extractStreet($object): void
    {
        $data  = \explode(' ', $object->cStrasse);
        $parts = \count($data);
        if ($parts > 1) {
            $object->cHausnummer = $data[$parts - 1];
            unset($data[$parts - 1]);
            $object->cStrasse = \implode(' ', $data);
        }
    }

    /**
     * @param string $oldSeo
     * @param string $newSeo
     * @return bool
     */
    protected function checkDbeSXmlRedirect($oldSeo, $newSeo): bool
    {
        // Insert into tredirect weil sich das SEO von der Kategorie geändert hat
        if ($oldSeo === $newSeo || $oldSeo === '' || $newSeo === '') {
            return false;
        }
        $redirect = new Redirect();

        return $redirect->saveExt('/' . $oldSeo, $newSeo, true);
    }
}
