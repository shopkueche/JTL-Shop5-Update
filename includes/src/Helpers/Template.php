<?php

namespace JTL\Helpers;

use Exception;
use InvalidArgumentException;
use JTL\Shop;
use JTL\Template\Config;
use JTL\Template\Model;
use JTL\Template\XMLReader;
use SimpleXMLElement;
use stdClass;

/**
 * Class Template
 * @package JTL\Helpers
 * @deprecated since 5.0.0
 */
class Template
{
    /**
     * @var string
     */
    public static $cTemplate;

    /**
     * @var int
     */
    public static $nVersion;

    /**
     * @var Template
     */
    private static $frontEndInstance;

    /**
     * @var string
     */
    private static $parent;

    /**
     * @var string
     */
    public $xmlData;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $preview;

    /**
     * @var XMLReader
     */
    private $reader;

    /**
     * @var Model
     */
    private $model;

    /**
     * Template constructor.
     * @throws Exception
     */
    public function __construct()
    {
        \trigger_error(__CLASS__ . ' is deprecated.', \E_USER_DEPRECATED);
        $this->init();
        self::$frontEndInstance = $this;
        $this->reader           = new XMLReader();
        $this->model            = Shop::Container()->getTemplateService()->getActiveTemplate();
        $this->xmlData          = $this->model;
    }

    /**
     * @return Template
     */
    public static function getInstance(): self
    {
        return self::$frontEndInstance ?? new self();
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'current_template';
        if (($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->loadFromModel($template);

            return $this;
        }
        try {
            $template = Shop::Container()->getTemplateService()->getActiveTemplate();
            $this->loadFromModel($template);
            Shop::Container()->getCache()->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);
        } catch (Exception $e) {
            throw new InvalidArgumentException('No template loaded - Exception: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * @param Model $model
     * @return $this
     */
    private function loadFromModel(Model $model): self
    {
        self::$cTemplate = $model->getTemplate();
        self::$parent    = !empty($model->getParent()) ? $model->getParent() : null;
        $this->name      = $model->getName();
        $this->author    = $model->getAuthor();
        $this->url       = $model->getUrl();
        $this->version   = $model->getVersion();
        $this->preview   = $model->getPreview();

        return $this;
    }

    /**
     * returns current template's name
     *
     * @return string|null
     */
    public function getFrontendTemplate(): ?string
    {
        $template = Model::loadByAttributes(['type' => 'standard'], Shop::Container()->getDB());

        self::$cTemplate = $template->getCTemplate();
        self::$parent    = $template->getParent();

        return self::$cTemplate;
    }

    /**
     * @param null|string $dir
     * @return null|SimpleXMLElement
     */
    public function leseXML($dir = null)
    {
        return $this->reader->getXML($dir ?? self::$cTemplate);
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources(): array
    {
        // @todo
        return [];
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array|mixed
     */
    public function getMinifyArray($absolute = false)
    {
        // @todo
        return [];
    }

    /**
     * @return bool
     */
    public function hasMobileTemplate(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMobileTemplateActive(): bool
    {
        return false;
    }

    /**
     * get current template's active skin
     *
     * @return string|null
     */
    public function getSkin(): ?string
    {
        return Shop::getSettings([\CONF_TEMPLATE])['template']['theme']['theme_default'] ?? null;
    }

    /**
     * @return $this
     */
    public function setzeKundenTemplate(): self
    {
        $this->init();

        return $this;
    }

    /**
     * @param string      $folder - the current template's dir name
     * @param string|null $parent
     * @return array
     */
    public function leseEinstellungenXML($folder, $parent = null): array
    {
        self::$cTemplate = $folder;

        return $this->reader->getConfigXML($folder, $parent);
    }

    /**
     * @param string|null $dirName
     * @return array
     */
    public function getBoxLayoutXML($dirName = null): array
    {
        $items  = [];
        $dirs   = self::$parent !== null ? [self::$parent] : [];
        $dirs[] = $dirName ?? self::$cTemplate;

        foreach ($dirs as $dir) {
            $xml = $this->reader->getXML($dir);
            if ($xml !== null && isset($xml->Boxes) && \count($xml->Boxes) === 1) {
                $boxXML = $xml->Boxes[0];
                foreach ($boxXML as $ditem) {
                    $cPosition         = (string)$ditem->attributes()->Position;
                    $bAvailable        = (bool)(int)$ditem->attributes()->Available;
                    $items[$cPosition] = $bAvailable;
                }
            }
        }

        return $items;
    }

    /**
     * @param string $dir
     * @return array
     */
    public function leseLessXML($dir): array
    {
        $xml       = $this->reader->getXML($dir);
        $lessFiles = [];
        if (!$xml || !isset($xml->Lessfiles)) {
            return $lessFiles;
        }
        /** @var SimpleXMLElement $oXMLTheme */
        foreach ($xml->Lessfiles->THEME as $oXMLTheme) {
            $theme             = new stdClass();
            $theme->cName      = (string)$oXMLTheme->attributes()->Name;
            $theme->oFiles_arr = [];
            foreach ($oXMLTheme->File as $cFile) {
                $oThemeFiles         = new stdClass();
                $oThemeFiles->cPath  = (string)$cFile->attributes()->Path;
                $theme->oFiles_arr[] = $oThemeFiles;
            }
            $lessFiles[$theme->cName] = $theme;
        }

        return $lessFiles;
    }

    /**
     * set new frontend template
     *
     * @param string $dir
     * @param string $eTyp
     * @return bool
     */
    public function setTemplate($dir, $eTyp = 'standard'): bool
    {
        Shop::Container()->getDB()->delete('ttemplate', 'eTyp', $eTyp);
        Shop::Container()->getDB()->delete('ttemplate', 'cTemplate', $dir);
        $tplConfig = $this->reader->getXML($dir);
        if ($tplConfig !== null && !empty($tplConfig->Parent)) {
            if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . (string)$tplConfig->Parent)) {
                return false;
            }
            self::$parent = (string)$tplConfig->Parent;
            $parentConfig = $this->reader->getXML(self::$parent);
        } else {
            $parentConfig = false;
        }

        $tplObject            = new stdClass();
        $tplObject->cTemplate = $dir;
        $tplObject->eTyp      = $eTyp;
        $tplObject->parent    = !empty($tplConfig->Parent)
            ? (string)$tplConfig->Parent
            : '_DBNULL_';
        $tplObject->name      = (string)$tplConfig->Name;
        $tplObject->author    = (string)$tplConfig->Author;
        $tplObject->url       = (string)$tplConfig->URL;
        $tplObject->version   = empty($tplConfig->Version) && $parentConfig
            ? $parentConfig->Version
            : $tplConfig->Version;
        $tplObject->preview   = (string)$tplConfig->Preview;
        if (empty($tplObject->version)) {
            $tplObject->version = !empty($tplConfig->ShopVersion)
                ? $tplConfig->ShopVersion
                : $parentConfig->ShopVersion;
        }
        $inserted = Shop::Container()->getDB()->insert('ttemplate', $tplObject);
        if ($inserted > 0) {
            if (!$dh = \opendir(\PFAD_ROOT . \PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = \readdir($dh)) !== false) {
                if (\mb_strpos($obj, '.') === 0) {
                    continue;
                }
                if (!\is_dir(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj)) {
                    \unlink(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj);
                }
            }
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $inserted > 0;
    }

    /**
     * get template configuration
     *
     * @return array|bool
     */
    public function getConfig()
    {
        return Shop::getSettings([\CONF_TEMPLATE])['template'];
    }

    /**
     * set template configuration
     *
     * @param string $dir
     * @param string $section
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setConfig($dir, $section, $name, $value): self
    {
        $config = new Config($dir, Shop::Container()->getDB());
        $config->updateConfigInDB($section, $name, $value);
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $this;
    }

    /**
     * @return bool
     */
    public function IsMobile(): bool
    {
        return false;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir($absolute = false): string
    {
        return $absolute ? (\PFAD_ROOT . \PFAD_TEMPLATES . self::$cTemplate) : self::$cTemplate;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return self::$parent;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getPreview(): ?string
    {
        return $this->preview;
    }

    /**
     * @param bool $bRedirect
     */
    public function check($bRedirect = true): void
    {
    }
}
