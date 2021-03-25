<?php declare(strict_types=1);


namespace JTL\Template;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Plugin\State;
use JTL\Shop;
use SimpleXMLElement;
use stdClass;
use function Functional\group;
use function Functional\select;

/**
 * Class Resources
 * @package JTL\Template
 */
class Resources
{
    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var SimpleXMLElement[]
     */
    private $xmlList;

    /**
     * @var array
     */
    private $cacheTags = [];

    /**
     * Resources constructor.
     * @param DbInterface           $db
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXML
     */
    public function __construct(DbInterface $db, SimpleXMLElement $xml, ?SimpleXMLElement $parentXML = null)
    {
        $this->db      = $db;
        $this->xmlList = [$parentXML, $xml];
    }

    public function __sleep(): array
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e) {
            return $e !== 'xmlList' && $e !== 'db';
        });
    }

    /**
     * the groups must not be initialized on instantiation because it depends on a fully loaded Shopsetting class
     * while the Shopsetting class loads the template model...
     */
    public function init(): void
    {
        if ($this->initialized === true) {
            return;
        }
        $this->initialized = true;
        $groups            = [
            'plugin_css'     => [],
            'plugin_js_head' => [],
            'plugin_js_body' => []
        ];
        foreach ($this->xmlList as $xml) {
            if ($xml === null) {
                continue;
            }
            $currentBaseDir = (string)$xml->dir;
            $cssSource      = $xml->Minify->CSS ?? [];
            $jsSource       = $xml->Minify->JS ?? [];
            foreach ($cssSource as $css) {
                /** @var SimpleXMLElement $css */
                $name = (string)$css->attributes()->Name;
                if (!isset($groups[$name])) {
                    $groups[$name] = [];
                }
                /** @var SimpleXMLElement $cssFile */
                foreach ($css->File as $cssFile) {
                    $file     = (string)$cssFile->attributes()->Path;
                    $filePath = \PFAD_ROOT . \PFAD_TEMPLATES . $currentBaseDir . '/' . $file;
                    if (\file_exists($filePath)
                        && (empty($cssFile->attributes()->DependsOnSetting)
                            || $this->checkCondition($cssFile) === true)
                    ) {
                        $_file      = \PFAD_TEMPLATES . $currentBaseDir . '/' . $cssFile->attributes()->Path;
                        $customFile = \str_replace('.css', '_custom.css', $filePath);
                        if (\file_exists($customFile)) { //add _custom file if existing
                            $_file           = \str_replace(
                                '.css',
                                '_custom.css',
                                \PFAD_TEMPLATES . $currentBaseDir . '/' . $cssFile->attributes()->Path
                            );
                            $groups[$name][] = [
                                'idx' => \str_replace('.css', '_custom.css', $cssFile->attributes()->Path),
                                'abs' => \realpath(\PFAD_ROOT . $_file),
                                'rel' => $_file
                            ];
                        } else { //otherwise add normal file
                            $groups[$name][] = [
                                'idx' => $file,
                                'abs' => \realpath(\PFAD_ROOT . $_file),
                                'rel' => $_file
                            ];
                        }
                    }
                }
            }
            foreach ($jsSource as $js) {
                /** @var SimpleXMLElement $js */
                $name = (string)$js->attributes()->Name;
                if (!isset($groups[$name])) {
                    $groups[$name] = [];
                }
                foreach ($js->File as $jsFile) {
                    if (!empty($jsFile->attributes()->DependsOnSetting) && $this->checkCondition($jsFile) !== true) {
                        continue;
                    }
                    $_file    = \PFAD_TEMPLATES . $currentBaseDir . '/' . $jsFile->attributes()->Path;
                    $newEntry = [
                        'idx' => (string)$jsFile->attributes()->Path,
                        'abs' => \PFAD_ROOT . $_file,
                        'rel' => $_file
                    ];
                    $found    = false;
                    if (!empty($jsFile->attributes()->override)
                        && (string)$jsFile->attributes()->override === 'true'
                    ) {
                        $idxToOverride = (string)$jsFile->attributes()->Path;
                        $max           = \count($groups[$name]);
                        for ($i = 0; $i < $max; $i++) {
                            if ($groups[$name][$i]['idx'] === $idxToOverride) {
                                $groups[$name][$i] = $newEntry;
                                $found             = true;
                                break;
                            }
                        }
                    }
                    if ($found === false) {
                        $groups[$name][] = $newEntry;
                    }
                }
            }
        }
        $pluginRes = $this->getPluginResources();
        foreach ($pluginRes['css'] as $_cssRes) {
            $customFile = \str_replace('.css', '_custom.css', $_cssRes->abs);
            if (\file_exists($customFile)) {
                $groups['plugin_css'][] = [
                    'idx' => $_cssRes->cName,
                    'abs' => $customFile,
                    'rel' => \str_replace('.css', '_custom.css', $_cssRes->rel)
                ];
            } else {
                $groups['plugin_css'][] = [
                    'idx' => $_cssRes->cName,
                    'abs' => $_cssRes->abs,
                    'rel' => $_cssRes->rel
                ];
            }
        }
        foreach ($pluginRes['js_head'] as $_jshRes) {
            $groups['plugin_js_head'][] = [
                'idx' => $_jshRes->cName,
                'abs' => $_jshRes->abs,
                'rel' => $_jshRes->rel
            ];
        }
        foreach ($pluginRes['js_body'] as $_jsbRes) {
            $groups['plugin_js_body'][] = [
                'idx' => $_jsbRes->cName,
                'abs' => $_jsbRes->abs,
                'rel' => $_jsbRes->rel
            ];
        }
        $cacheTags = [\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE, \CACHING_GROUP_PLUGIN];
        \executeHook(\HOOK_CSS_JS_LIST, [
            'groups'     => &$groups,
            'cache_tags' => &$cacheTags
        ]);
        $this->cacheTags = $cacheTags;
        $this->groups    = $groups;
    }

    /**
     * get resource path for single plugins
     *
     * @param stdClass[] $items
     * @return array
     */
    private function getPluginResourcesPath(array $items): array
    {
        foreach ($items as $item) {
            $frontend = \PFAD_PLUGIN_FRONTEND . $item->type . '/' . $item->path;
            if ((int)$item->bExtension === 1) {
                $item->rel = \PLUGIN_DIR . $item->cVerzeichnis . '/';
            } else {
                $item->rel = \PFAD_PLUGIN . $item->cVerzeichnis . '/';
                $frontend  = \PFAD_PLUGIN_VERSION . $item->nVersion . '/' . $frontend;
            }
            $item->rel .= $frontend;
            $item->abs  = \PFAD_ROOT . $item->rel;
        }

        return $items;
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources(): array
    {
        $resourcesc = $this->db->queryPrepared(
            'SELECT * 
                FROM tplugin_resources AS res
                JOIN tplugin
                    ON tplugin.kPlugin = res.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY res.priority DESC',
            ['state' => State::ACTIVATED],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $grouped    = group($resourcesc, static function ($e) {
            return $e->type;
        });
        if (isset($grouped['js'])) {
            $grouped['js'] = group($grouped['js'], static function ($e) {
                return $e->position;
            });
        }

        return [
            'css'     => $this->getPluginResourcesPath($grouped['css'] ?? []),
            'js_head' => $this->getPluginResourcesPath($grouped['js']['head'] ?? []),
            'js_body' => $this->getPluginResourcesPath($grouped['js']['body'] ?? [])
        ];
    }

    /**
     * parse node of js/css files for insertion conditions and validate them
     *
     * @param SimpleXMLElement $node
     * @return bool
     */
    private function checkCondition(SimpleXMLElement $node): bool
    {
        $attrs         = $node->attributes();
        $settingsGroup = \constant((string)$attrs->DependsOnSettingGroup);
        $settingValue  = (string)$attrs->DependsOnSettingValue;
        $comparator    = (string)$attrs->DependsOnSettingComparison;
        $setting       = (string)$attrs->DependsOnSetting;
        $conf          = Shop::getSettings([$settingsGroup]);
        $hierarchy     = \explode('.', $setting);
        $iterations    = \count($hierarchy);
        $i             = 0;
        if (empty($comparator)) {
            $comparator = '==';
        }
        foreach ($hierarchy as $_h) {
            $conf = $conf[$_h] ?? null;
            if ($conf === null) {
                return false;
            }
            if (++$i === $iterations) {
                switch ($comparator) {
                    case '==':
                        return $conf == $settingValue;
                    case '===':
                        return $conf === $settingValue;
                    case '>=':
                        return $conf >= $settingValue;
                    case '<=':
                        return $conf <= $settingValue;
                    case '>':
                        return $conf > $settingValue;
                    case '<':
                        return $conf < $settingValue;
                    default:
                        return false;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * get array of static resources in minify compatible format
     * @param bool $absolute
     * @return array
     */
    public function getMinifyArray(bool $absolute = false): array
    {
        if (!$this->initialized) {
            $this->init();
        }
        $res = [];
        foreach ($this->getGroups() as $name => $_tplGroup) {
            $res[$name] = [];
            foreach ($_tplGroup as $_file) {
                $res[$name][] = $absolute === true ? $_file['abs'] : $_file['rel'];
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * @param array $cacheTags
     */
    public function setCacheTags(array $cacheTags): void
    {
        $this->cacheTags = $cacheTags;
    }
}
