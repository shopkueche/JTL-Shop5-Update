<?php

namespace JTL\Backend;

use JTL\Shop;
use JTL\Template\XMLReader;
use SimpleXMLElement;

/**
 * Class AdminTemplate
 * @package JTL\Backend
 */
class AdminTemplate
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
     * @var AdminTemplate
     */
    private static $instance;

    /**
     * @var bool
     */
    private static $isAdmin = true;

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
     * @var int
     */
    public $version;

    /**
     * @var string
     */
    public $preview;

    /**
     *
     */
    public function __construct()
    {
        $this->init();
        self::$instance = $this;
    }

    /**
     * @return $this
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * get template configuration
     *
     * @return array|bool
     */
    public function getConfig()
    {
        return false;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir(bool $absolute = false): string
    {
        return $absolute
            ? (\PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . self::$cTemplate)
            : self::$cTemplate;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'crnt_tpl_adm';
        if (($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            self::$cTemplate = $template->cTemplate;
        } else {
            $template = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'admin');
            //dump('$oTemplate', $oTemplate);
            if ($template) {
                self::$cTemplate = $template->cTemplate;
                Shop::Container()->getCache()->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);

                return $this;
            }
            // fall back to admin template "default"
            self::$cTemplate = 'bootstrap';
        }

        return $this;
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array
     */
    public function getMinifyArray(bool $absolute = false): array
    {
        $dir       = $this->getDir();
        $folders   = [];
        $folders[] = $dir;
        $cacheID   = 'tpl_mnfy_dta_adm_' . $dir . (($absolute === true) ? '_a' : '');
        if (($tplGroups = Shop::Container()->getCache()->get($cacheID)) === false) {
            $tplGroups = [
                'admin_css' => [],
                'admin_js'  => []
            ];
            $reader    = new XMLReader();
            foreach ($folders as $dir) {
                $xml = $reader->getXML($dir, true);
                if ($xml === null) {
                    continue;
                }
                $cssSource = $xml->Minify->CSS ?? [];
                $jsSource  = $xml->Minify->JS ?? [];
                /** @var SimpleXMLElement $css */
                foreach ($cssSource as $css) {
                    $name = (string)$css->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($css->File as $cssFile) {
                        $file     = (string)$cssFile->attributes()->Path;
                        $filePath = self::$isAdmin === false
                            ? \PFAD_ROOT . \PFAD_TEMPLATES . $xml->dir . '/' . $file
                            : \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $xml->dir . '/' . $file;
                        if (\file_exists($filePath)) {
                            $tplGroups[$name][] = ($absolute === true ? \PFAD_ROOT : '') .
                                (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                                \PFAD_TEMPLATES . $dir . '/' . (string)$cssFile->attributes()->Path;
                            $customFilePath     = \str_replace('.css', '_custom.css', $filePath);
                            if (\file_exists($customFilePath)) {
                                $tplGroups[$name][] = \str_replace(
                                    '.css',
                                    '_custom.css',
                                    ($absolute === true ? \PFAD_ROOT : '') .
                                    (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                                    \PFAD_TEMPLATES . $dir . '/' . (string)$cssFile->attributes()->Path
                                );
                            }
                        }
                    }
                    // assign custom.css
                    $customFilePath = \PFAD_ROOT . 'templates/' . $xml->dir . '/themes/custom.css';
                    if (\file_exists($customFilePath)) {
                        $tplGroups[$name][] = (($absolute === true) ? \PFAD_ROOT : '') .
                            (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                            \PFAD_TEMPLATES . $dir . '/' . 'themes/custom.css';
                    }
                }
                foreach ($jsSource as $js) {
                    $name = (string)$js->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($js->File as $jsFile) {
                        $tplGroups[$name][] = ($absolute === true ? \PFAD_ROOT : '') .
                            (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                            \PFAD_TEMPLATES . $dir . '/' . (string)$jsFile->attributes()->Path;
                    }
                }
            }
            $cacheTags = [\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE, \CACHING_GROUP_PLUGIN];
            if (!self::$isAdmin) {
                \executeHook(\HOOK_CSS_JS_LIST, ['groups' => &$tplGroups, 'cache_tags' => &$cacheTags]);
            }
            Shop::Container()->getCache()->set($cacheID, $tplGroups, $cacheTags);
        }

        return $tplGroups;
    }

    /**
     * build string to serve minified files or direct head includes
     *
     * @param bool $minify - generates absolute links for minify when true
     * @return array - list of js/css resources
     */
    public function getResources(bool $minify = true): array
    {
        self::$isAdmin = true;
        $outputCSS     = '';
        $outputJS      = '';
        $baseURL       = Shop::getURL();
        $version       = empty($this->version) ? '1.0.0' : $this->version;
        $files         = $this->getMinifyArray($minify);
        if ($minify === false) {
            $fileSuffix = '?v=' . $version;
            foreach ($files['admin_js'] as $_file) {
                $outputJS .= '<script type="text/javascript" src="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '"></script>'
                    . "\n";
            }
            foreach ($files['admin_css'] as $_file) {
                $outputCSS .= '<link rel="stylesheet" type="text/css" href="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '" media="screen" />'
                    . "\n";
            }
        } else {
            $tplString  = $this->getDir(); // add tpl string to avoid caching
            $fileSuffix = '&v=' . $version;
            $outputCSS  = '<link rel="stylesheet" type="text/css" href="'
                . $baseURL . '/'
                . \PFAD_MINIFY . '/index.php?g=admin_css&tpl='
                . $tplString
                . $fileSuffix
                . '" media="screen" />';
            $outputJS   = '<script type="text/javascript" src="'
                . $baseURL . '/'
                . \PFAD_MINIFY
                . '/index.php?g=admin_js&tpl='
                . $tplString
                . $fileSuffix
                . '"></script>';
        }

        return ['js' => $outputJS, 'css' => $outputCSS];
    }
}
