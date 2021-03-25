<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Helpers\PaymentMethod;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\Data\Config;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use stdClass;

/**
 * Class PaymentMethods
 * @package JTL\Plugin\Admin\Installation\Items
 */
class PaymentMethods extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['PaymentMethod'][0]['Method'])
        && \is_array($this->baseNode['Install'][0]['PaymentMethod'][0]['Method'])
        && \count($this->baseNode['Install'][0]['PaymentMethod'][0]['Method']) > 0
            ? $this->baseNode['Install'][0]['PaymentMethod'][0]['Method']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $base     = $this->plugin->bExtension === 1
            ? \PLUGIN_DIR . $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_PAYMENTMETHOD
            : \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION .
            $this->plugin->nVersion . '/' . \PFAD_PLUGIN_PAYMENTMETHOD;
        $shopURL  = Shop::getURL(true) . '/';
        $pluginID = $this->plugin->kPlugin;
        foreach ($this->getNode() as $i => $data) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            $method                         = new stdClass();
            $method->cName                  = $data['Name'];
            $method->cModulId               = Helper::getModuleIDByPluginID($pluginID, $data['Name']);
            $method->cKundengruppen         = '';
            $method->cPluginTemplate        = $data['TemplateFile'] ?? null;
            $method->cZusatzschrittTemplate = $data['AdditionalTemplateFile'] ?? null;
            $method->nSort                  = isset($data['Sort'])
                ? (int)$data['Sort']
                : 0;
            $method->nMailSenden            = isset($data['SendMail'])
                ? (int)$data['SendMail']
                : 0;
            $method->nActive                = 1;
            $method->cAnbieter              = \is_array($data['Provider']) ? '' : $data['Provider'];
            $method->cTSCode                = \is_array($data['TSCode']) ? '' : $data['TSCode'];
            $method->nWaehrendBestellung    = (int)$data['PreOrder'];
            $method->nCURL                  = (int)$data['Curl'];
            $method->nSOAP                  = (int)$data['Soap'];
            $method->nSOCKETS               = (int)$data['Sockets'];
            $method->cBild                  = isset($data['PictureURL'])
                ? $shopURL . $base . $data['PictureURL']
                : '';
            $method->nNutzbar               = 0;
            $methodID                       = $this->db->insert('tzahlungsart', $method);
            $method->kZahlungsart           = $methodID;
            $method->nNutzbar               = PaymentMethod::activatePaymentMethod($method) ? 1 : 0;

            $moduleID = $method->cModulId;
            if (!$methodID) {
                return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD;
            }
            $paymentClass                         = new stdClass();
            $paymentClass->cModulId               = Helper::getModuleIDByPluginID($pluginID, $data['Name']);
            $paymentClass->kPlugin                = $pluginID;
            $paymentClass->cClassPfad             = $data['ClassFile'] ?? null;
            $paymentClass->cClassName             = $data['ClassName'] ?? null;
            $paymentClass->cTemplatePfad          = $data['TemplateFile'] ?? null;
            $paymentClass->cZusatzschrittTemplate = $data['AdditionalTemplateFile'] ?? null;

            $this->db->insert('tpluginzahlungsartklasse', $paymentClass);

            $iso          = '';
            $allLanguages = LanguageHelper::getAllLanguages(2);
            $default      = false;
            $localized    = new stdClass();
            foreach ($data['MethodLanguage'] as $l => $loc) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    $iso = \mb_convert_case($loc['iso'], \MB_CASE_LOWER);
                } elseif (\mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    $localizedMethod                   = new stdClass();
                    $localizedMethod->kZahlungsart     = $methodID;
                    $localizedMethod->cISOSprache      = $iso;
                    $localizedMethod->cName            = $loc['Name'];
                    $localizedMethod->cGebuehrname     = $loc['ChargeName'];
                    $localizedMethod->cHinweisText     = $loc['InfoText'];
                    $localizedMethod->cHinweisTextShop = $loc['InfoText'];
                    if (!$default) {
                        $localized = $localizedMethod;
                        $default   = true;
                    }
                    $tmpID = $this->db->insert('tzahlungsartsprache', $localizedMethod);
                    if (!$tmpID) {
                        return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LOCALIZATION;
                    }

                    if (isset($allLanguages[$localizedMethod->cISOSprache])) {
                        unset($allLanguages[$localizedMethod->cISOSprache]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            foreach ($allLanguages as $language) {
                $localized->cISOSprache = $language->cISO;
                $tmpID                  = $this->db->insert(
                    'tzahlungsartsprache',
                    $localized
                );
                if (!$tmpID) {
                    return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LANGUAGE;
                }
            }
            $names        = ['Anzahl Bestellungen nötig', 'Mindestbestellwert', 'Maximaler Bestellwert'];
            $valueNames   = ['min_bestellungen', 'min', 'max'];
            $descriptions = [
                'Nur Kunden, die min. soviele Bestellungen bereits durchgeführt haben, ' .
                'können diese Zahlungsart nutzen.',
                'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.',
                'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)'
            ];
            $sorting      = [100, 101, 102];
            for ($z = 0; $z < 3; $z++) {
                $conf          = new stdClass();
                $conf->kPlugin = $pluginID;
                $conf->cName   = $moduleID . '_' . $valueNames[$z];
                $conf->cWert   = 0;
                $this->db->insert('tplugineinstellungen', $conf);
                $plgnConf                   = new stdClass();
                $plgnConf->kPlugin          = $pluginID;
                $plgnConf->kPluginAdminMenu = 0;
                $plgnConf->cName            = $names[$z];
                $plgnConf->cBeschreibung    = $descriptions[$z];
                $plgnConf->cWertName        = $moduleID . '_' . $valueNames[$z];
                $plgnConf->cInputTyp        = 'zahl';
                $plgnConf->nSort            = $sorting[$z];
                $plgnConf->cConf            = 'Y';

                $this->db->insert('tplugineinstellungenconf', $plgnConf);
            }
            $configCode = $this->addConfig($data, $pluginID, $moduleID);
            if ($configCode !== InstallCode::OK) {
                return $configCode;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $data
     * @param int    $pluginID
     * @param string $moduleID
     * @return int
     */
    private function addConfig(array $data, int $pluginID, string $moduleID): int
    {
        if (!GeneralObject::hasCount('Setting', $data)) {
            return InstallCode::OK;
        }
        $type         = '';
        $initialValue = '';
        $sort         = 0;
        $configurable = 'Y';
        $multiple     = false;
        foreach ($data['Setting'] as $j => $config) {
            $j = (string)$j;
            \preg_match('/[0-9]+\sattr/', $j, $hits3);
            \preg_match('/[0-9]+/', $j, $hits4);
            if (isset($hits3[0]) && \mb_strlen($hits3[0]) === \mb_strlen($j)) {
                $type         = $config['type'];
                $multiple     = (isset($config['multiple'])
                    && $config['multiple'] === 'Y'
                    && $type === InputType::SELECT);
                $initialValue = $multiple === true
                    ? \serialize([$config['initialValue']])
                    : $config['initialValue'];
                $sort         = $config['sort'];
                $configurable = $config['conf'];
            } elseif (\mb_strlen($hits4[0]) === \mb_strlen($j)) {
                $conf          = new stdClass();
                $conf->kPlugin = $pluginID;
                $conf->cName   = $moduleID . '_' . $config['ValueName'];
                $conf->cWert   = $initialValue;
                if ($this->db->select('tplugineinstellungen', 'cName', $conf->cName) !== null) {
                    $this->db->update(
                        'tplugineinstellungen',
                        'cName',
                        $conf->cName,
                        $conf
                    );
                } else {
                    $this->db->insert('tplugineinstellungen', $conf);
                }
                $plgnConf                   = new stdClass();
                $plgnConf->kPlugin          = $pluginID;
                $plgnConf->kPluginAdminMenu = 0;
                $plgnConf->cName            = $config['Name'];
                $plgnConf->cBeschreibung    = (!isset($config['Description'])
                    || \is_array($config['Description']))
                    ? ''
                    : $config['Description'];
                $plgnConf->cWertName        = $moduleID . '_' . $config['ValueName'];
                $plgnConf->cInputTyp        = $type;
                $plgnConf->nSort            = $sort;
                $plgnConf->cConf            = ($type === InputType::SELECT && $multiple === true)
                    ? Config::TYPE_DYNAMIC
                    : $configurable;
                $plgnConfTmpID              = $this->db->select(
                    'tplugineinstellungenconf',
                    'cWertName',
                    $plgnConf->cWertName
                );
                if ($plgnConfTmpID !== null) {
                    $this->db->update(
                        'tplugineinstellungenconf',
                        'cWertName',
                        $plgnConf->cWertName,
                        $plgnConf
                    );
                    $configID = (int)$plgnConfTmpID->kPluginEinstellungenConf;
                } else {
                    $configID = $this->db->insert(
                        'tplugineinstellungenconf',
                        $plgnConf
                    );
                }
                // tplugineinstellungenconfwerte füllen
                if ($configID <= 0) {
                    return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_SETTING;
                }
                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                if ($type === InputType::SELECT) {
                    if (GeneralObject::hasCount('OptionsSource', $config)) {
                        //do nothing for now
                    } elseif (\count($config['SelectboxOptions'][0]) === 1) {
                        foreach ($config['SelectboxOptions'][0]['Option'] as $y => $option) {
                            $y = (string)$y;
                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                            if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                $value = $option['value'];
                                $sort  = $option['sort'];
                                $yx    = \mb_substr($y, 0, \mb_strpos($y, ' '));
                                $name  = $config['SelectboxOptions'][0]['Option'][$yx];

                                $plgnConfValues                           = new stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $configID;
                                $plgnConfValues->cName                    = $name;
                                $plgnConfValues->cWert                    = $value;
                                $plgnConfValues->nSort                    = $sort;

                                $this->db->insert(
                                    'tplugineinstellungenconfwerte',
                                    $plgnConfValues
                                );
                            }
                        }
                    } elseif (\count($config['SelectboxOptions'][0]) === 2) {
                        $idx                                      = $config['SelectboxOptions'][0];
                        $plgnConfValues                           = new stdClass();
                        $plgnConfValues->kPluginEinstellungenConf = $configID;
                        $plgnConfValues->cName                    = $idx['Option'];
                        $plgnConfValues->cWert                    = $idx['Option attr']['value'];
                        $plgnConfValues->nSort                    = $idx['Option attr']['sort'];

                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                    }
                } elseif ($type === InputType::RADIO) {
                    if (GeneralObject::hasCount('OptionsSource', $config)) {
                        //do nothing for now
                    } elseif (\count($config['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                        foreach ($config['RadioOptions'][0]['Option'] as $y => $option) {
                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                            if (\mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                $value = $option['value'];
                                $sort  = $option['sort'];
                                $yx    = \mb_substr($y, 0, \mb_strpos($y, ' '));
                                $name  = $config['RadioOptions'][0]['Option'][$yx];

                                $plgnConfValues                           = new stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $configID;
                                $plgnConfValues->cName                    = $name;
                                $plgnConfValues->cWert                    = $value;
                                $plgnConfValues->nSort                    = $sort;

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        }
                    } elseif (\count($config['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                        $idx                                      = $config['RadioOptions'][0];
                        $plgnConfValues                           = new stdClass();
                        $plgnConfValues->kPluginEinstellungenConf = $configID;
                        $plgnConfValues->cName                    = $idx['Option'];
                        $plgnConfValues->cWert                    = $idx['Option attr']['value'];
                        $plgnConfValues->nSort                    = $idx['Option attr']['sort'];

                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
