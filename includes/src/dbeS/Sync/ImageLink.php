<?php

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Media\Image\Product;
use SimpleXMLElement;
use function Functional\flatten;
use function Functional\map;

/**
 * Class ImageLink
 * @package JTL\dbeS\Sync
 */
final class ImageLink extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $productIDs = [];
        foreach ($starter->getXML(true) as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_bildartikellink.xml') !== false) {
                $productIDs[] = $this->handleDeletes($xml);
            } elseif (\strpos($file, 'bildartikellink.xml') !== false) {
                $productIDs[] = $this->handleInserts($xml);
            }
        }
        $productIDs = \array_unique(flatten($productIDs));
        Product::clearCache($productIDs);
        $this->cache->flushTags(map($productIDs, static function ($pid) {
            return \CACHING_GROUP_ARTICLE . '_' . $pid;
        }));

        return null;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function handleInserts(SimpleXMLElement $xml): array
    {
        $productIDs = [];
        foreach ($this->getArray($xml) as $item) {
            // delete link first. Important because jtl-wawi does not send del_bildartikellink when image is updated.
            $this->db->delete(
                'tartikelpict',
                ['kArtikel', 'nNr'],
                [(int)$item->kArtikel, (int)$item->nNr]
            );
            $productIDs[] = (int)$item->kArtikel;
            $this->upsert('tartikelpict', [$item], 'kArtikelPict');
        }

        return $productIDs;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function handleDeletes(SimpleXMLElement $xml): array
    {
        $productIDs = [];
        foreach ($this->getItemsToDelete($xml) as $item) {
            $this->deleteImageItem($item);
            $productIDs[] = $item->kArtikel;
        }

        return $productIDs;
    }

    /**
     * @param \stdClass $item
     */
    private function deleteImageItem($item): void
    {
        $image = $this->db->select('tartikelpict', 'kArtikel', $item->kArtikel, 'nNr', $item->nNr);
        if (!\is_object($image)) {
            return;
        }
        // is last reference
        $res = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM tartikelpict WHERE kBild = ' . (int)$image->kBild,
            ReturnType::SINGLE_OBJECT
        );
        if ((int)$res->cnt === 1) {
            $this->db->delete('tbild', 'kBild', (int)$image->kBild);
            $storage = \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE . $image->cPfad;
            if (\file_exists($storage)) {
                @\unlink($storage);
            }
        }
        $this->db->delete(
            'tartikelpict',
            ['kArtikel', 'nNr'],
            [(int)$item->kArtikel, (int)$item->nNr]
        );
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function getItemsToDelete(SimpleXMLElement $xml): array
    {
        $items = [];
        foreach ($xml->children() as $child) {
            $items[] = (object)[
                'nNr'      => (int)$child->nNr,
                'kArtikel' => (int)$child->kArtikel
            ];
        }

        return $items;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function getArray(SimpleXMLElement $xml): array
    {
        $items = [];
        foreach ($xml->children() as $child) {
            $item    = (object)[
                'cPfad'        => '',
                'kBild'        => (int)$child->attributes()->kBild,
                'nNr'          => (int)$child->attributes()->nNr,
                'kArtikel'     => (int)$child->attributes()->kArtikel,
                'kArtikelPict' => (int)$child->attributes()->kArtikelPict
            ];
            $imageID = (int)$child->attributes()->kBild;
            $image   = $this->db->select('tbild', 'kBild', $imageID);
            if (\is_object($image)) {
                $item->cPfad = $image->cPfad;
                $items[]     = $item;
            } else {
                $this->logger->debug('Missing reference in tbild (Key: ' . $imageID . ')');
            }
        }

        return $items;
    }
}
