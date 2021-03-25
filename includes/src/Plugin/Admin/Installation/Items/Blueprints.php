<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\OPC\InputType;
use JTL\Plugin\InstallCode;
use JTL\Shop;

/**
 * Class Blueprints
 * @package JTL\Plugin\Admin\Installation\Items
 */
class Blueprints extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
        && \is_array($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
            ? $this->baseNode['Install'][0]['Blueprints'][0]['Blueprint']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $base = $this->plugin->bExtension === 1
            ? \PFAD_ROOT . \PLUGIN_DIR .
            $this->plugin->cVerzeichnis . '/' .
            \PFAD_PLUGIN_BLUEPRINTS
            : \PFAD_ROOT . \PFAD_PLUGIN .
            $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION .
            $this->plugin->nVersion . '/' . \PFAD_PLUGIN_BLUEPRINTS;
        foreach ($this->getNode() as $i => $blueprint) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            $blueprintJson = \file_get_contents($base . $blueprint['JSONFile']);
            $blueprintData = \json_decode($blueprintJson, true);
            $this->copyBlueprintImages($base, $blueprintData['instance']);
            $instanceJson = \json_encode($blueprintData['instance']);
            $blueprintObj = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cName'   => $blueprint['Name'],
                'cJson'   => $instanceJson,
            ];
            if (!$this->db->insert('topcblueprint', $blueprintObj)) {
                return InstallCode::SQL_CANNOT_SAVE_BLUEPRINT;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param string $base
     * @param array  $instanceData
     * @throws \Exception
     */
    protected function copyBlueprintImages($base, &$instanceData)
    {
        $class   = $instanceData['class'];
        $portlet = Shop::Container()->getOPC()->createPortletInstance($class);
        $props   = $portlet->getPortlet()->getDeepPropertyDesc();

        foreach ($props as $name => $prop) {
            if (isset($instanceData['properties'][$name], $prop['type'])) {
                if ($prop['type'] === InputType::IMAGE) {
                    if (\is_file($base . $instanceData['properties'][$name])) {
                        $oldname = $instanceData['properties'][$name];
                        $newname = $this->plugin->cVerzeichnis . '_' . $oldname;
                        \copy(
                            $base . $oldname,
                            \PFAD_ROOT . \STORAGE_OPC . $newname
                        );
                        $instanceData['properties'][$name] = $newname;
                    }
                } elseif ($prop['type'] === InputType::IMAGE_SET) {
                    foreach ($instanceData['properties'][$name] as $i => &$image) {
                        if (\is_file($base . $image['url'])) {
                            $oldname = $image['url'];
                            $newname = $this->plugin->cVerzeichnis . '_' . $oldname;
                            \copy(
                                $base . $oldname,
                                \PFAD_ROOT . \STORAGE_OPC . $newname
                            );
                            $image['url'] = $newname;
                        }
                    }
                    unset($image);
                }
            }
        }

        if (isset($instanceData['subareas'])) {
            foreach ($instanceData['subareas'] as &$subarea) {
                foreach ($subarea['content'] as &$subportlet) {
                    $this->copyBlueprintImages($base, $subportlet);
                }
            }
        }
    }
}
