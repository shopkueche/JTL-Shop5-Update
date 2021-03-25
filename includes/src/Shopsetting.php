<?php

namespace JTL;

use ArrayAccess;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use function Functional\reindex;

/**
 * Class Shopsetting
 * @package JTL
 */
final class Shopsetting implements ArrayAccess
{
    /**
     * @var Shopsetting
     */
    private static $instance;

    /**
     * @var array
     */
    private $container = [];

    /**
     * @var array
     */
    private $allSettings;

    /**
     * @var array
     */
    private static $mapping = [
        \CONF_GLOBAL              => 'global',
        \CONF_STARTSEITE          => 'startseite',
        \CONF_EMAILS              => 'emails',
        \CONF_ARTIKELUEBERSICHT   => 'artikeluebersicht',
        \CONF_ARTIKELDETAILS      => 'artikeldetails',
        \CONF_KUNDEN              => 'kunden',
        \CONF_LOGO                => 'logo',
        \CONF_KAUFABWICKLUNG      => 'kaufabwicklung',
        \CONF_BOXEN               => 'boxen',
        \CONF_BILDER              => 'bilder',
        \CONF_SONSTIGES           => 'sonstiges',
        \CONF_ZAHLUNGSARTEN       => 'zahlungsarten',
        \CONF_PLUGINZAHLUNGSARTEN => 'pluginzahlungsarten',
        \CONF_KONTAKTFORMULAR     => 'kontakt',
        \CONF_SHOPINFO            => 'shopinfo',
        \CONF_RSS                 => 'rss',
        \CONF_VERGLEICHSLISTE     => 'vergleichsliste',
        \CONF_PREISVERLAUF        => 'preisverlauf',
        \CONF_BEWERTUNG           => 'bewertung',
        \CONF_NEWSLETTER          => 'newsletter',
        \CONF_KUNDENFELD          => 'kundenfeld',
        \CONF_NAVIGATIONSFILTER   => 'navigationsfilter',
        \CONF_EMAILBLACKLIST      => 'emailblacklist',
        \CONF_METAANGABEN         => 'metaangaben',
        \CONF_NEWS                => 'news',
        \CONF_SITEMAP             => 'sitemap',
        \CONF_SUCHSPECIAL         => 'suchspecials',
        \CONF_TEMPLATE            => 'template',
        \CONF_CHECKBOX            => 'checkbox',
        \CONF_AUSWAHLASSISTENT    => 'auswahlassistent',
        \CONF_CRON                => 'cron',
        \CONF_FS                  => 'fs',
        \CONF_CACHING             => 'caching',
        \CONF_CONSENTMANAGER      => 'consentmanager',
        \CONF_BRANDING            => 'branding'
    ];

    /**
     * Shopsetting constructor.
     */
    private function __construct()
    {
        self::$instance = $this;
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return Shopsetting
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * for rare cases when options are modified and directly re-assigned to smarty
     * do not call this function otherwise.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->container = [];

        return $this;
    }

    /**
     * @param string $offset
     * @param mixed  $value
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }

        return $this;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param string $offset
     * @return $this
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);

        return $this;
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        if (isset($this->container[$offset])) {
            return $this->container[$offset];
        }
        $section = static::mapSettingName(null, $offset);
        $cacheID = 'setting_' . $section;
        if ($section === false || $section === null) {
            return null;
        }
        if ($section === \CONF_TEMPLATE) {
            $settings = Shop::Container()->getCache()->get(
                $cacheID,
                function ($cache, $id, &$content, &$tags) {
                    $content = $this->getTemplateConfig(Shop::Container()->getDB());
                    $tags    = [\CACHING_GROUP_TEMPLATE, \CACHING_GROUP_OPTION];

                    return true;
                }
            );
            if (\is_array($settings)) {
                foreach ($settings as $templateSection => $templateSetting) {
                    $this->container[$offset][$templateSection] = $templateSetting;
                }
            }
        } elseif ($section === \CONF_BRANDING) {
            return Shop::Container()->getCache()->get(
                $cacheID,
                function ($cache, $id, &$content, &$tags) {
                    $content = $this->getBrandingConfig(Shop::Container()->getDB());
                    $tags    = [\CACHING_GROUP_OPTION];

                    return true;
                }
            );
        } else {
            $settings = Shop::Container()->getCache()->get(
                $cacheID,
                function ($cache, $id, &$content, &$tags) use ($section) {
                    $content = $this->getSectionData($section);
                    $tags    = [\CACHING_GROUP_OPTION];

                    return true;
                }
            );
            if (\count($settings) > 0) {
                $this->addContainerData($offset, $settings);
            }
        }

        return $this->container[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @param array  $settings
     */
    private function addContainerData($offset, array $settings): void
    {
        $this->container[$offset] = [];
        foreach ($settings as $setting) {
            if ($setting->type === 'listbox') {
                if (!isset($this->container[$offset][$setting->cName])) {
                    $this->container[$offset][$setting->cName] = [];
                }
                $this->container[$offset][$setting->cName][] = $setting->cWert;
            } elseif ($setting->type === 'number') {
                $this->container[$offset][$setting->cName] = (int)$setting->cWert;
            } else {
                $this->container[$offset][$setting->cName] = $setting->cWert;
            }
        }
    }

    /**
     * @param string $section
     * @return array
     */
    private function getSectionData($section): array
    {
        if ($section === \CONF_PLUGINZAHLUNGSARTEN) {
            return Shop::Container()->getDB()->query(
                "SELECT cName, cWert, '' AS type
                     FROM tplugineinstellungen
                     WHERE cName LIKE '%_min%' 
                        OR cName LIKE '%_max'",
                ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT teinstellungen.cName, teinstellungen.cWert, teinstellungenconf.cInputTyp AS type
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                WHERE teinstellungen.kEinstellungenSektion = :section',
            ['section' => $section],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param array|int $sections
     * @return array
     */
    public function getSettings($sections): array
    {
        $ret = [];
        if (!\is_array($sections)) {
            $sections = (array)$sections;
        }
        foreach ($sections as $section) {
            $mapping = self::mapSettingName($section);
            if ($mapping !== null) {
                $ret[$mapping] = $this[$mapping];
            }
        }

        return $ret;
    }

    /**
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public function getValue(int $section, $option)
    {
        $settings    = $this->getSettings([$section]);
        $sectionName = self::mapSettingName($section);

        return $settings[$sectionName][$option] ?? null;
    }

    /**
     * @param null|string $section
     * @param null|string $name
     * @return mixed|null
     */
    public static function mapSettingName($section = null, $name = null)
    {
        if ($section === null && $name === null) {
            return false;
        }
        if ($section !== null && isset(self::$mapping[$section])) {
            return self::$mapping[$section];
        }
        if ($name !== null && ($key = \array_search($name, self::$mapping, true)) !== false) {
            return $key;
        }

        return null;
    }

    /**
     * @param DbInterface $db
     * @return array
     */
    private function getBrandingConfig(DbInterface $db): array
    {
        $data = $db->query(
            'SELECT tbranding.kBranding AS id, tbranding.cBildKategorie AS type, 
            tbrandingeinstellung.cPosition AS position, tbrandingeinstellung.cBrandingBild AS path,
            tbrandingeinstellung.dTransparenz AS transparency, tbrandingeinstellung.dGroesse AS size
                FROM tbrandingeinstellung
                INNER JOIN tbranding 
                    ON tbrandingeinstellung.kBranding = tbranding.kBranding
                WHERE tbrandingeinstellung.nAktiv = 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            $item->size         = (int)$item->size;
            $item->transparency = (int)$item->transparency;
            $item->path         = \PFAD_ROOT . \PFAD_BRANDINGBILDER . $item->path;
        }

        return reindex($data, static function ($e) {
            return $e->type;
        });
    }

    /**
     * @param DbInterface $db
     * @return array
     */
    private function getTemplateConfig(DbInterface $db): array
    {
        $data     = $db->query(
            "SELECT cSektion AS sec, cWert AS val, cName AS name 
                FROM ttemplateeinstellungen 
                WHERE cTemplate = (SELECT cTemplate FROM ttemplate WHERE eTyp = 'standard')",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $settings = [];
        foreach ($data as $setting) {
            if (!isset($settings[$setting->sec])) {
                $settings[$setting->sec] = [];
            }
            $settings[$setting->sec][$setting->name] = $setting->val;
        }

        return $settings;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        if ($this->allSettings !== null) {
            return $this->allSettings;
        }
        $db       = Shop::Container()->getDB();
        $result   = [];
        $settings = $db->query(
            'SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, teinstellungen.cWert,
                teinstellungenconf.cInputTyp AS type
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                ORDER BY kEinstellungenSektion',
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        foreach (self::$mapping as $mappingID => $sectionName) {
            foreach ($settings as $setting) {
                $sectionID = (int)$setting['kEinstellungenSektion'];
                if ($sectionID === $mappingID) {
                    if (!isset($result[$sectionName])) {
                        $result[$sectionName] = [];
                    }
                    if ($setting['type'] === 'listbox') {
                        if (!isset($result[$sectionName][$setting['cName']])) {
                            $result[$sectionName][$setting['cName']] = [];
                        }
                        $result[$sectionName][$setting['cName']][] = $setting['cWert'];
                    } elseif ($setting['type'] === 'number') {
                        $result[$sectionName][$setting['cName']] = (int)$setting['cWert'];
                    } else {
                        $result[$sectionName][$setting['cName']] = $setting['cWert'];
                    }
                }
            }
        }
        $result['template'] = $this->getTemplateConfig($db);
        $result['branding'] = $this->getBrandingConfig($db);
        $this->allSettings  = $result;

        return $result;
    }

    /**
     * preload the _container variable with one single sql statement or one single cache call
     * this is being called after successful cache initialisation in class.JTL-Shop.JTLCache.php
     *
     * @return array
     */
    public function preLoad(): array
    {
        $cacheID           = 'settings_all_preload';
        $result            = Shop::Container()->getCache()->get(
            $cacheID,
            function ($cache, $id, &$content, &$tags) {
                $content = $this->getAll();
                $tags    = [\CACHING_GROUP_TEMPLATE, \CACHING_GROUP_OPTION, \CACHING_GROUP_CORE];

                return true;
            }
        );
        $this->container   = $result;
        $this->allSettings = $result;

        return $result;
    }
}
