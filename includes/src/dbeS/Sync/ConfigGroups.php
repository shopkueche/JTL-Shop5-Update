<?php

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;
use JTL\Extensions\Config\Group;

/**
 * Class Configurations
 * @package JTL\dbeS\Sync
 */
final class ConfigGroups extends AbstractSync
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
            if ($fileName === 'del_konfig.xml') {
                $this->handleDeletes($xml);
            } elseif ($fileName === 'konfig.xml') {
                $this->handleInserts($xml);
            }
        }

        return null;
    }

    /**
     * @param object $xml
     */
    private function handleInserts($xml): void
    {
        foreach ($xml->tkonfiggruppe as $groupData) {
            $group = $this->mapper->map($groupData, 'mKonfigGruppe');
            $this->upsert('tkonfiggruppe', [$group], 'kKonfiggruppe');
            foreach ($groupData->tkonfiggruppesprache as $localized) {
                $this->upsert(
                    'tkonfiggruppesprache',
                    [$this->mapper->map($localized, 'mKonfigSprache')],
                    'kKonfiggruppe',
                    'kSprache'
                );
            }
            $this->deleteConfigItem((int)$group->kKonfiggruppe);
            foreach ($groupData->tkonfigitem as $item) {
                $this->upsert(
                    'tkonfigitem',
                    [$this->mapper->map($item, 'mKonfigItem')],
                    'kKonfigitem'
                );
                foreach ($item->tkonfigitemsprache as $localized) {
                    $this->upsert(
                        'tkonfigitemsprache',
                        [$this->mapper->map($localized, 'mKonfigSprache')],
                        'kKonfigitem',
                        'kSprache'
                    );
                }
                foreach ($item->tkonfigitempreis as $price) {
                    $this->upsert(
                        'tkonfigitempreis',
                        [$this->mapper->map($price, 'mKonfigItemPreis')],
                        'kKonfigitem',
                        'kKundengruppe'
                    );
                }
            }
        }
    }

    /**
     * @param object $xml
     */
    private function handleDeletes($xml): void
    {
        if (!Group::checkLicense()) {
            return;
        }
        foreach (\array_map('\intval', $xml->kKonfiggruppe) as $groupID) {
            $this->deleteGroup($groupID);
        }
    }

    /**
     * @param int $id
     */
    private function deleteGroup(int $id): void
    {
        $this->db->delete('tkonfiggruppe', 'kKonfiggruppe', $id);
    }

    /**
     * @param int $id
     */
    private function deleteConfigItem(int $id): void
    {
        $this->db->delete('tkonfigitem', 'kKonfiggruppe', $id);
    }
}
