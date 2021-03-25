<?php

namespace JTL\Helpers;

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class SeoHelper
 * @package JTL\Helpers
 */
class Seo
{
    /**
     * @param string $url
     * @return string
     */
    public static function getSeo($url): string
    {
        return \is_string($url) ? self::sanitizeSeoSlug($url) : '';
    }

    /**
     * @param string $url
     * @return string
     */
    public static function checkSeo($url): string
    {
        if (!$url || !\is_string($url)) {
            return '';
        }
        $exists = Shop::Container()->getDB()->select('tseo', 'cSeo', $url);
        if ($exists === null) {
            return $url;
        }
        Shop::Container()->getDB()->query('SET @IKEY := 0', ReturnType::QUERYSINGLE);
        $obj = Shop::Container()->getDB()->query(
            "SELECT oseo.newSeo
                FROM (
                    SELECT CONCAT('{$url}', '_', @IKEY:=@IKEY+1) newSeo, @IKEY nOrder
                    FROM tseo AS iseo
                    WHERE iseo.cSeo LIKE '{$url}%'
                        AND iseo.cSeo RLIKE '^{$url}(_[0-9]+)?$'
                ) AS oseo
                WHERE oseo.newSeo NOT IN (
                    SELECT iseo.cSeo
                    FROM tseo AS iseo
                    WHERE iseo.cSeo LIKE '{$url}_%'
                        AND iseo.cSeo RLIKE '^{$url}_[0-9]+$'
                )
                ORDER BY oseo.nOrder
                LIMIT 1",
            ReturnType::SINGLE_OBJECT
        );

        return $obj->newSeo ?? $url;
    }

    /**
     * @param string $str
     * @var mixed $convertedStr
     * @return mixed
     */
    public static function sanitizeSeoSlug(string $str): string
    {
        // for better german slugs without using setlocale()
        $a = ['Ä', 'Ö', 'Ü', 'ß', 'ä', 'ö', 'ü', 'æ'];
        $b = ['Ae', 'Oe', 'Ue', 'ss', 'ae', 'oe', 'ue', 'ae'];

        $str          = \preg_replace('/[^\pL\d\-\/_\s]+/u', '', \str_replace($a, $b, $str));
        $str          = \preg_replace('/[\/]+/u', '/', $str);
        $str          = \transliterator_transliterate(
            'Any-Latin; Latin-ASCII;' . (\SEO_SLUG_LOWERCASE ? ' Lower();' : ''),
            \trim($str, ' -_')
        );
        $convertedStr = @\iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str          = $convertedStr === false ? \preg_replace('/[^a-zA-Z0-9\s]/', '', $str) : $convertedStr;
        $str          = \preg_replace('/[\-_\s]+/u', '-', \trim($str));

        return $str;
    }

    /**
     * Get flat SEO-URL path (removes all slashes from seo-url-path, including leading and trailing slashes)
     *
     * @param string $path - the seo path e.g. "My/Product/Name"
     * @return string - flat SEO-URL Path e.g. "My-Product-Name"
     */
    public static function getFlatSeoPath($path): string
    {
        return \trim(\str_replace('/', '-', $path), ' -_');
    }
}
