<?php

namespace JTL\Smarty;

use JTL\Language\LanguageHelper;

/**
 * Class PluginCollection
 * @package JTL\Smarty
 */
class PluginCollection
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var LanguageHelper
     */
    private $lang;

    /**
     * PluginCollection constructor.
     *
     * @param array          $config
     * @param LanguageHelper $lang
     */
    public function __construct(array $config, LanguageHelper $lang)
    {
        $this->config = $config;
        $this->lang   = $lang;
    }

    /**
     * @param array $params
     * @return string
     */
    public function compilerModifierDefault(array $params): string
    {
        $output = $params[0];
        if (!isset($params[1])) {
            $params[1] = "''";
        }
        \array_shift($params);
        foreach ($params as $param) {
            $output = '(($tmp = ' . $output . ' ?? null)===null||$tmp===\'\' ? ' . $param . ' : $tmp)';
        }

        return $output;
    }

    /**
     * @param string $string
     * @return string
     */
    public function replaceDelimiters(string $string): string
    {
        $replace = $this->config['global']['global_dezimaltrennzeichen_sonstigeangaben'];
        if ($replace !== ',' && $replace !== '.') {
            $replace = ',';
        }

        return \str_replace('.', $replace, $string);
    }

    /**
     * @param string $string
     * @param int    $length
     * @param string $etc
     * @param bool   $break
     * @param bool   $middle
     * @return string
     */
    public function truncate(
        string $string,
        int $length = 80,
        string $etc = '...',
        bool $break = false,
        bool $middle = false
    ): string {
        if ($length === 0) {
            return '';
        }
        if (\mb_strlen($string) > $length) {
            $length -= \min($length, \mb_strlen($etc));
            if (!$break && !$middle) {
                $string = \preg_replace('/\s+?(\S+)?$/', '', \mb_substr($string, 0, $length + 1));
            }

            return !$middle
                ? \mb_substr($string, 0, $length) . $etc
                : \mb_substr($string, 0, $length / 2) . $etc . \mb_substr($string, -$length / 2);
        }

        return $string;
    }

    /**
     * translation
     *
     * @param array $params
     * @param \Smarty_Internal_Template $template
     * @return void|string
     */
    public function translate(array $params, \Smarty_Internal_Template $template)
    {
        $res     = '';
        $section = $params['section'] ?? 'global';
        $key     = $params['key'] ?? '';
        if ($key !== '') {
            $res = $this->lang->get($key, $section);
            // Für vsprintf ein String der :: exploded wird
            if (isset($params['printf'])) {
                $res = \vsprintf($res, \explode(':::', $params['printf']));
            }
        }
        if (\SMARTY_SHOW_LANGKEY) {
            $res = '#' . $section . '.' . $key . '#';
        }
        if (isset($params['assign'])) {
            $template->assign($params['assign'], $res);
        } else {
            return $res;
        }
    }

    /**
     * @param string|null $text
     * @return int
     */
    public function countCharacters(?string $text): int
    {
        return \mb_strlen($text ?? '');
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    public function stringFormat(string $string, string $format): string
    {
        return \sprintf($format, $string);
    }

    /**
     * @param string $string
     * @param string $format
     * @param string $default_date
     * @return string
     */
    public function dateFormat(string $string, string $format = '%b %e, %Y', string $default_date = ''): string
    {
        if ($string !== '') {
            $timestamp = \smarty_make_timestamp($string);
        } elseif ($default_date !== '') {
            $timestamp = \smarty_make_timestamp($default_date);
        } else {
            return $string;
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            $_win_from = ['%D', '%h', '%n', '%r', '%R', '%t', '%T'];
            $_win_to   = ['%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S'];
            if (\mb_strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[]   = \sprintf('%\' 2d', \date('j', $timestamp));
            }
            if (\mb_strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[]   = \sprintf('%\' 2d', \date('h', $timestamp));
            }
            $format = \str_replace($_win_from, $_win_to, $format);
        }

        return \strftime($format, $timestamp);
    }

    /**
     * @param array $params
     * @param mixed $content
     * @return string
     */
    public function inlineScript(array $params, $content): string
    {
        if ($content === null || empty(\trim($content))) {
            return '';
        }
        $content = \preg_replace('/^<script(.*?)>/', '', \trim($content));
        $content = \preg_replace('/<\/script>$/', '', $content);

        return '<script defer src="data:text/javascript;base64,' . \base64_encode($content) . '"></script>';
    }
}
