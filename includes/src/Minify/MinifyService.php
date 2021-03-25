<?php declare(strict_types=1);

namespace JTL\Minify;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Model;

/**
 * Class MinifyService
 * @package JTL\Minify
 */
class MinifyService
{
    /**
     * @var string
     */
    protected $baseDir = \PFAD_ROOT . \PATH_STATIC_MINIFY;

    public const TYPE_CSS = 'css';

    public const TYPE_JS = 'js';

    /**
     * MinifyService constructor.
     */
    public function __construct()
    {
        if (!\is_dir($this->baseDir) && !\mkdir($this->baseDir) && !\is_dir($this->baseDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $this->baseDir));
        }
    }

    /**
     * Build a URI for the static cache
     *
     * @param string      $urlPrefix E.g. "/min/static"
     * @param string      $query E.g. "b=scripts&f=1.js,2.js"
     * @param string      $type "css" or "js"
     * @param string|null $cacheTime
     * @return string
     */
    public function buildURI($urlPrefix, $query, $type, string $cacheTime = null): string
    {
        $urlPrefix = \rtrim($urlPrefix, '/');
        $query     = \ltrim($query, '?');
        $ext       = '.' . $type;
        $cacheTime = $cacheTime ?? $this->getCacheTime();
        if (\substr($query, -\strlen($ext)) !== $ext) {
            $query .= '&z=' . $ext;
        }

        return $urlPrefix . '/' . $cacheTime . '/' . $query;
    }

    /**
     * Get the name of the current cache directory within static/. E.g. "1467089473"
     *
     * @param bool $autoCreate Automatically create the directory if missing?
     * @return null|string null if missing or can't create
     */
    protected function getCacheTime(bool $autoCreate = true): ?string
    {
        foreach (\scandir($this->baseDir) as $entry) {
            if (\ctype_digit($entry)) {
                return $entry;
            }
        }

        if (!$autoCreate) {
            return null;
        }
        $time = (string)\time();
        $dir  = $this->baseDir . $time;
        if (!\mkdir($dir) && !\is_dir($dir)) {
            return null;
        }

        return $time;
    }

    /**
     * @return bool
     */
    public function flushCache(): bool
    {
        $time = $this->getCacheTime(false);

        return $time ? $this->removeTree($this->baseDir . $time) : false;
    }

    /**
     * @param string $dir
     * @return bool
     */
    protected function removeTree(string $dir): bool
    {
        foreach (\array_diff(\scandir($dir), ['.', '..']) as $file) {
            $path = $dir . \DIRECTORY_SEPARATOR . $file;
            \is_dir($path) ? $this->removeTree($path) : \unlink($path);
        }

        return \rmdir($dir);
    }

    /**
     * @param JTLSmarty $smarty
     * @param Model     $template
     * @param string    $themeDir
     */
    public function buildURIs(JTLSmarty $smarty, Model $template, string $themeDir): void
    {
        $minify      = $template->getResources()->getMinifyArray();
        $tplVersion  = $template->getVersion();
        $config      = Shop::getConfig([\CONF_TEMPLATE])['template'];
        $allowStatic = isset($config['general']['use_minify']) && $config['general']['use_minify'] === 'static';
        $cacheTime   = $allowStatic ? $this->getCacheTime() : null;
        $css         = $minify[$themeDir . '.css'] ?? [];
        $js          = $minify['jtl3.js'] ?? [];
        $res         = [];
        $data        = [
            self::TYPE_CSS => [
                $themeDir . '.css',
                'plugin_css',
            ],
            self::TYPE_JS  => [
                'jtl3.js',
                'plugin_js_head',
                'plugin_js_body'
            ]
        ];
        \executeHook(\HOOK_LETZTERINCLUDE_CSS_JS, [
            'cCSS_arr'          => &$css,
            'cJS_arr'           => &$js,
            'cPluginCss_arr'    => &$minify['plugin_css'],
            'cPluginJsHead_arr' => &$minify['plugin_js_head'],
            'cPluginJsBody_arr' => &$minify['plugin_js_body'],
        ]);
        foreach ($data as $type => $groups) {
            $res[$type] = [];
            foreach ($groups as $group) {
                if (!isset($minify[$group]) || \count($minify[$group]) === 0) {
                    continue;
                }
                if ($allowStatic === true) {
                    $uri = $this->buildURI('static', 'g=' . $group, $type, $cacheTime);
                } else {
                    $uri = 'asset/' . $group . '?v=' . $tplVersion;
                }
                $res[$type][$group] = $uri;
            }
        }
        if ($allowStatic === true) {
            $uri = 'g=' . $themeDir . '.css';
            if (isset($minify['plugin_css']) && \count($minify['plugin_css']) > 0) {
                $uri .= ',plugin_css';
            }
            $combinedCSS = $this->buildURI('static', $uri, self::TYPE_CSS, $cacheTime);
        } else {
            $combinedCSS = 'asset/' . $themeDir . '.css';
            if (\count($minify['plugin_css']) > 0) {
                $combinedCSS .= ',plugin_css';
            }
            $combinedCSS .= '?v=' . $tplVersion;
        }

        $smarty->assign('cPluginCss_arr', $minify['plugin_css'])
            ->assign('cPluginJsHead_arr', $minify['plugin_js_head'])
            ->assign('cPluginJsBody_arr', $minify['plugin_js_body'])
            ->assign('minifiedCSS', $res[self::TYPE_CSS])
            ->assign('minifiedJS', $res[self::TYPE_JS])
            ->assign('combinedCSS', $combinedCSS)
            ->assign('cCSS_arr', $css)
            ->assign('cJS_arr', $js);
    }
}
