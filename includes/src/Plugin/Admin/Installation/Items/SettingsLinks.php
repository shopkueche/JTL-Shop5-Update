<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\Data\Config;
use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class SettingsLinks
 * @package JTL\Plugin\Admin\Installation\Items
 */
class SettingsLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Adminmenu'])
        && \is_array($this->baseNode['Install'][0]['Adminmenu'])
            ? $this->baseNode['Install'][0]['Adminmenu']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $node     = $this->getNode();
        $pluginID = $this->plugin->kPlugin;
        if (!isset($node[0]['Settingslink'])
            || !\is_array($node[0]['Settingslink'])
            || \count($node[0]['Settingslink']) === 0
        ) {
            return InstallCode::OK;
        }
        $sort = 0;
        foreach ($node[0]['Settingslink'] as $i => $settingsLinks) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                $sort = (int)$settingsLinks['sort'];
            } elseif (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                $menuItem             = new stdClass();
                $menuItem->kPlugin    = $pluginID;
                $menuItem->cName      = $settingsLinks['Name'];
                $menuItem->cDateiname = '';
                $menuItem->nSort      = $sort;
                $menuItem->nConf      = 1;

                $menuID = $this->db->insert('tpluginadminmenu', $menuItem);
                if ($menuID <= 0) {
                    return InstallCode::SQL_CANNOT_SAVE_SETTINGS_ITEM;
                }
                $type         = '';
                $initialValue = '';
                $sort         = 0;
                $cConf        = 'Y';
                $multiple     = false;
                foreach ($settingsLinks['Setting'] as $j => $setting) {
                    $j = (string)$j;
                    \preg_match('/[0-9]+\sattr/', $j, $hits3);
                    \preg_match('/[0-9]+/', $j, $hits4);
                    if (isset($hits3[0]) && \mb_strlen($hits3[0]) === \mb_strlen($j)) {
                        $type         = $setting['type'];
                        $multiple     = (isset($setting['multiple'])
                            && $setting['multiple'] === 'Y'
                            && $type === InputType::SELECT);
                        $initialValue = '';
                        if (isset($setting['initialValue'])) {
                            $initialValue = ($multiple === true)
                                ? \serialize([$setting['initialValue']])
                                : $setting['initialValue'];
                        }
                        $sort  = $setting['sort'];
                        $cConf = $setting['conf'];
                    } elseif (\mb_strlen($hits4[0]) === \mb_strlen($j)) {
                        $plgnConf          = new stdClass();
                        $plgnConf->kPlugin = $pluginID;
                        $plgnConf->cName   = \is_array($setting['ValueName'])
                            ? $setting['ValueName']['0']
                            : $setting['ValueName'];
                        $plgnConf->cWert   = $initialValue;
                        $exists            = $this->db->select(
                            'tplugineinstellungen',
                            ['cName', 'kPlugin'],
                            [$plgnConf->cName, $plgnConf->kPlugin]
                        );

                        if ($exists !== null) {
                            $this->db->update(
                                'tplugineinstellungen',
                                ['cName', 'kPlugin'],
                                [$plgnConf->cName, $plgnConf->kPlugin],
                                $plgnConf
                            );
                        } else {
                            $this->db->insert('tplugineinstellungen', $plgnConf);
                        }
                        $plgnConf                   = new stdClass();
                        $plgnConf->kPlugin          = $pluginID;
                        $plgnConf->kPluginAdminMenu = $menuID;
                        $plgnConf->cName            = $setting['Name'];
                        $plgnConf->cBeschreibung    = (!isset($setting['Description'])
                            || \is_array($setting['Description']))
                            ? ''
                            : $setting['Description'];
                        $plgnConf->cWertName        = \is_array($setting['ValueName'])
                            ? $setting['ValueName']['0']
                            : $setting['ValueName'];
                        $plgnConf->cInputTyp        = $type;
                        $plgnConf->nSort            = $sort;
                        $plgnConf->cConf            = $cConf;
                        //dynamic data source for selectbox/radio
                        if ($type === InputType::SELECT || $type === InputType::RADIO) {
                            if (isset($setting['OptionsSource'][0]['File'])) {
                                $plgnConf->cSourceFile = $setting['OptionsSource'][0]['File'];
                            }
                            if ($multiple === true) {
                                $plgnConf->cConf = Config::TYPE_DYNAMIC;
                            }
                        }
                        $plgnConfTmpID = $this->db->select(
                            'tplugineinstellungenconf',
                            ['kPlugin', 'cWertName'],
                            [$plgnConf->kPlugin, $plgnConf->cWertName]
                        );
                        if ($plgnConfTmpID !== null) {
                            $this->db->update(
                                'tplugineinstellungenconf',
                                ['kPlugin', 'cWertName'],
                                [$plgnConf->kPlugin, $plgnConf->cWertName],
                                $plgnConf
                            );
                            $confID = $plgnConfTmpID->kPluginEinstellungenConf;
                        } else {
                            $confID = $this->db->insert(
                                'tplugineinstellungenconf',
                                $plgnConf
                            );
                        }
                        if ($confID <= 0) {
                            return InstallCode::SQL_CANNOT_SAVE_SETTING;
                        }
                        $sort = 0;
                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                        if ($type === InputType::SELECT) {
                            $optNode = $setting['SelectboxOptions'][0] ?? [];
                            if (GeneralObject::hasCount('OptionsSource', $setting)) {
                                //do nothing for now
                            } elseif (\count($optNode) === 1) { // Es gibt mehr als eine Option
                                foreach ($optNode['Option'] as $y => $option) {
                                    $y = (string)$y;
                                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                    if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                        $value = $option['value'];
                                        $sort  = $option['sort'];
                                        $yx    = \mb_substr($y, 0, \mb_strpos($y, ' '));
                                        $name  = $optNode['Option'][$yx];

                                        $plgnConfValues                           = new stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $confID;
                                        $plgnConfValues->cName                    = $name;
                                        $plgnConfValues->cWert                    = $value;
                                        $plgnConfValues->nSort                    = $sort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                                    }
                                }
                            } elseif (\count($optNode) === 2) { // Es gibt nur eine Option
                                $plgnConfValues                           = new stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    = $optNode['Option'];
                                $plgnConfValues->cWert                    = $optNode['Option attr']['value'];
                                $plgnConfValues->nSort                    = $optNode['Option attr']['sort'];
                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        } elseif ($type === InputType::RADIO) {
                            $optNode = $setting['RadioOptions'][0] ?? [];
                            if (GeneralObject::hasCount('OptionsSource', $setting)) {
                            } elseif (\count($optNode) === 1) { // Es gibt mehr als eine Option
                                foreach ($optNode['Option'] as $y => $option) {
                                    $y = (string)$y;
                                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                    if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                        $value = $option['value'];
                                        $sort  = $option['sort'];
                                        $yx    = \mb_substr($y, 0, \mb_strpos($y, ' '));
                                        $name  = $optNode['Option'][$yx];

                                        $plgnConfValues                           = new stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $confID;
                                        $plgnConfValues->cName                    = $name;
                                        $plgnConfValues->cWert                    = $value;
                                        $plgnConfValues->nSort                    = $sort;

                                        $this->db->insert(
                                            'tplugineinstellungenconfwerte',
                                            $plgnConfValues
                                        );
                                    }
                                }
                            } elseif (\count($optNode) === 2) { // Es gibt nur eine Option
                                $plgnConfValues                           = new stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    = $optNode['Option'];
                                $plgnConfValues->cWert                    = $optNode['Option attr']['value'];
                                $plgnConfValues->nSort                    = $optNode['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
