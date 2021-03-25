<?php

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Shop;
use stdClass;
use function Functional\map;

/**
 * Class QuickSync
 * @package JTL\dbeS\Sync
 */
final class QuickSync extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'quicksync.xml') !== false) {
                $this->handleInserts($xml);
            }
        }
        $this->db->query('COMMIT', ReturnType::DEFAULT);

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $source = $xml['quicksync']['tartikel'] ?? null;
        if (!\is_array($source)) {
            return;
        }
        $products = $this->mapper->mapArray($xml['quicksync'], 'tartikel', 'mArtikelQuickSync');
        $count    = \count($products);
        if ($count < 2) {
            $this->handleNewPriceFormat((int)$products[0]->kArtikel, $source);
            $this->handlePriceHistory((int)$products[0]->kArtikel, $source);
        } else {
            for ($i = 0; $i < $count; ++$i) {
                $this->handleNewPriceFormat((int)$products[$i]->kArtikel, $source[$i]);
                $this->handlePriceHistory((int)$products[$i]->kArtikel, $source[$i]);
            }
        }
        $this->insertProducts($products);
    }

    /**
     * @param array $products
     */
    private function insertProducts(array $products): void
    {
        $clearTags = [];
        $conf      = Shop::getSettings([\CONF_ARTIKELDETAILS]);
        foreach ($products as $product) {
            $id = (int)$product->kArtikel;
            if (isset($product->fLagerbestand) && $product->fLagerbestand > 0) {
                $delta = $this->db->query(
                    "SELECT SUM(pos.nAnzahl) AS totalquantity
                    FROM tbestellung b
                    JOIN twarenkorbpos pos
                    ON pos.kWarenkorb = b.kWarenkorb
                    WHERE b.cAbgeholt = 'N'
                        AND pos.kArtikel = " . $id,
                    ReturnType::SINGLE_OBJECT
                );
                if ($delta->totalquantity > 0) {
                    $product->fLagerbestand -= $delta->totalquantity;
                }
            }

            if ($product->fLagerbestand < 0) {
                $product->fLagerbestand = 0;
            }

            $upd                        = new stdClass();
            $upd->fLagerbestand         = $product->fLagerbestand;
            $upd->fStandardpreisNetto   = $product->fStandardpreisNetto;
            $upd->dLetzteAktualisierung = 'NOW()';
            $this->db->update('tartikel', 'kArtikel', $id, $upd);
            \executeHook(\HOOK_QUICKSYNC_XML_BEARBEITEINSERT, ['oArtikel' => $product]);
            $parentProduct = $this->db->select(
                'tartikel',
                'kArtikel',
                $id,
                null,
                null,
                null,
                null,
                false,
                'kVaterArtikel'
            );
            if (!empty($parentProduct->kVaterArtikel)) {
                $clearTags[] = (int)$parentProduct->kVaterArtikel;
            }
            $clearTags[] = $id;
            $this->sendAvailabilityMails($product, $conf);
        }
        $clearTags = \array_unique($clearTags);
        $this->cache->flushTags(map($clearTags, static function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));
    }
}
