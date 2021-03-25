<?php

namespace JTL\Catalog\Product;

use DateTime;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Preisverlauf
 * @package JTL\Catalog\Product
 */
class Preisverlauf
{
    /**
     * @var int
     */
    public $kPreisverlauf;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fPreisPrivat;

    /**
     * @var float
     */
    public $fPreisHaendler;

    /**
     * @var string
     */
    public $dDate;

    /**
     * Preisverlauf constructor.
     *
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @param int $month
     * @return mixed
     */
    public function gibPreisverlauf(int $productID, int $customerGroupID, int $month)
    {
        $cacheID = 'gpv_' . $productID . '_' . $customerGroupID . '_' . $month;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data     = Shop::Container()->getDB()->query(
                'SELECT tpreisverlauf.fVKNetto, tartikel.fMwst, UNIX_TIMESTAMP(tpreisverlauf.dDate) AS timestamp
                    FROM tpreisverlauf 
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tpreisverlauf.kArtikel
                    WHERE tpreisverlauf.kArtikel = ' . $productID . '
                        AND tpreisverlauf.kKundengruppe = ' . $customerGroupID . '
                        AND DATE_SUB(NOW(), INTERVAL ' . $month . ' MONTH) < tpreisverlauf.dDate
                    ORDER BY tpreisverlauf.dDate DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $currency = Frontend::getCurrency();
            $dt       = new DateTime();
            foreach ($data as $pv) {
                if (isset($pv->timestamp)) {
                    $dt->setTimestamp((int)$pv->timestamp);
                    $pv->date     = $dt->format('d.m.Y');
                    $pv->fPreis   = Frontend::getCustomerGroup()->isMerchant()
                        ? \round($pv->fVKNetto * $currency->getConversionFactor(), 2)
                        : Tax::getGross($pv->fVKNetto * $currency->getConversionFactor(), $pv->fMwst);
                    $pv->currency = $currency->getCode();
                }
            }
            Shop::Container()->getCache()->set(
                $cacheID,
                $data,
                [\CACHING_GROUP_ARTICLE, \CACHING_GROUP_ARTICLE . '_' . $productID]
            );
        }

        return $data;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $item = Shop::Container()->getDB()->select('tpreisverlauf', 'kPreisverlauf', $id);
        if ($item !== null) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $ins = GeneralObject::copyMembers($this);
        unset($ins->kPreisverlauf);
        $this->kPreisverlauf = Shop::Container()->getDB()->insert('tpreisverlauf', $ins);

        return $this->kPreisverlauf;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $upd = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update('tpreisverlauf', 'kPreisverlauf', $upd->kPreisverlauf, $upd);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
