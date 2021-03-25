<?php

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;

/**
 * Class DeliveryNotes
 * @package JTL\dbeS\Sync
 */
final class DeliveryNotes extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML(true) as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            $fileName     = \pathinfo($file)['basename'];
            if ($fileName === 'lief.xml') {
                $this->handleInserts($xml);
            } elseif ($fileName === 'del_lief.xml') {
                $this->handleDeletes($xml);
            }
        }

        return null;
    }

    /**
     * @param object $xml
     */
    private function handleInserts($xml): void
    {
        foreach ($xml->tlieferschein as $item) {
            $deliveryNote = $this->mapper->map($item, 'mLieferschein');
            if ((int)$deliveryNote->kInetBestellung <= 0) {
                continue;
            }
            $deliveryNote->dErstellt = \date_format(\date_create($deliveryNote->dErstellt), 'U');
            $this->upsert('tlieferschein', [$deliveryNote], 'kLieferschein');

            foreach ($item->tlieferscheinpos as $xmlItem) {
                $sItem                = $this->mapper->map($xmlItem, 'mLieferscheinpos');
                $sItem->kLieferschein = $deliveryNote->kLieferschein;
                $this->upsert('tlieferscheinpos', [$sItem], 'kLieferscheinPos');

                foreach ($xmlItem->tlieferscheinposInfo as $info) {
                    $posInfo                   = $this->mapper->map($info, 'mLieferscheinposinfo');
                    $posInfo->kLieferscheinPos = $sItem->kLieferscheinPos;
                    $this->upsert('tlieferscheinposinfo', [$posInfo], 'kLieferscheinPosInfo');
                }
            }

            foreach ($item->tversand as $shipping) {
                $shipping                = $this->mapper->map($shipping, 'mVersand');
                $shipping->kLieferschein = $deliveryNote->kLieferschein;
                $shipping->dErstellt     = \date_format(\date_create($shipping->dErstellt), 'U');
                $this->upsert('tversand', [$shipping], 'kVersand');
            }
        }
    }

    /**
     * @param object $xml
     */
    private function handleDeletes($xml): void
    {
        $items = $xml->kLieferschein;
        if (!\is_array($items)) {
            $items = (array)$items;
        }
        foreach (\array_filter(\array_map('\intval', $items)) as $id) {
            $this->db->delete('tversand', 'kLieferschein', $id);
            $this->db->delete('tlieferschein', 'kLieferschein', $id);
            foreach ($this->db->selectAll(
                'tlieferscheinpos',
                'kLieferschein',
                $id,
                'kLieferscheinPos'
            ) as $item) {
                $this->db->delete(
                    'tlieferscheinpos',
                    'kLieferscheinPos',
                    (int)$item->kLieferscheinPos
                );
                $this->db->delete(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$item->kLieferscheinPos
                );
            }
        }
    }
}
