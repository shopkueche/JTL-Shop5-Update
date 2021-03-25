<?php

namespace JTL\Helpers;

use Exception;

/**
 * Class Strings
 * @package JTL\Helpers
 */
class Text
{
    /**
     * @var array
     */
    private static $mappings = [
        'aar' => 'aa', // Afar
        'abk' => 'ab', // Abkhazian
        'afr' => 'af', // Afrikaans
        'aka' => 'ak', // Akan
        'alb' => 'sq', // Albanian
        'amh' => 'am', // Amharic
        'ara' => 'ar', // Arabic
        'arg' => 'an', // Aragonese
        'arm' => 'hy', // Armenian
        'asm' => 'as', // Assamese
        'ava' => 'av', // Avaric
        'ave' => 'ae', // Avestan
        'aym' => 'ay', // Aymara
        'aze' => 'az', // Azerbaijani
        'bak' => 'ba', // Bashkir
        'bam' => 'bm', // Bambara
        'baq' => 'eu', // Basque
        'bel' => 'be', // Belarusian
        'ben' => 'bn', // Bengali
        'bih' => 'bh', // Bihari languages
        'bis' => 'bi', // Bislama
        'bos' => 'bs', // Bosnian
        'bre' => 'br', // Breton
        'bul' => 'bg', // Bulgarian
        'bur' => 'my', // Burmese
        'cat' => 'ca', // Catalan; Valencian
        'cze' => 'cs', // Czech
        'cha' => 'ch', // Chamorro
        'che' => 'ce', // Chechen
        'chi' => 'zh', // Chinese
        'chu' => 'cu', // Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic
        'chv' => 'cv', // Chuvash
        'cor' => 'kw', // Cornish
        'cos' => 'co', // Corsican
        'cre' => 'cr', // Cree
        'dan' => 'da', // Danish
        'div' => 'dv', // Divehi; Dhivehi; Maldivian
        'dut' => 'nl', // Dutch; Flemish
        'dzo' => 'dz', // Dzongkha
        'eng' => 'en', // English
        'epo' => 'eo', // Esperanto
        'est' => 'et', // Estonian
        'ewe' => 'ee', // Ewe
        'fao' => 'fo', // Faroese
        'fij' => 'fj', // Fijian
        'fin' => 'fi', // Finnish
        'fre' => 'fr', // French
        'fry' => 'fy', // Western Frisian
        'ful' => 'ff', // Fulah
        'geo' => 'ka', // Georgian
        'ger' => 'de', // German
        'gla' => 'gd', // Gaelic; Scottish Gaelic
        'gle' => 'ga', // Irish
        'glg' => 'gl', // Galician
        'glv' => 'gv', // Manx
        'gre' => 'el', // Greek, Modern (1453-)
        'grn' => 'gn', // Guarani
        'guj' => 'gu', // Gujarati
        'hat' => 'ht', // Haitian; Haitian Creole
        'hau' => 'ha', // Hausa
        'heb' => 'he', // Hebrew
        'her' => 'hz', // Herero
        'hin' => 'hi', // Hindi
        'hmo' => 'ho', // Hiri Motu
        'hrv' => 'hr', // Croatian
        'hun' => 'hu', // Hungarian
        'ibo' => 'ig', // Igbo
        'ice' => 'is', // Icelandic
        'ido' => 'io', // Ido
        'iii' => 'ii', // Sichuan Yi; Nuosu
        'iku' => 'iu', // Inuktitut
        'ile' => 'ie', // Interlingue; Occidental
        'ina' => 'ia', // Interlingua (International Auxiliary Language Association)
        'ind' => 'id', // Indonesian
        'ipk' => 'ik', // Inupiaq
        'ita' => 'it', // Italian
        'jav' => 'jv', // Javanese
        'jpn' => 'ja', // Japanese
        'kal' => 'kl', // Kalaallisut; Greenlandic
        'kan' => 'kn', // Kannada
        'kas' => 'ks', // Kashmiri
        'kau' => 'kr', // Kanuri
        'kaz' => 'kk', // Kazakh
        'khm' => 'km', // Central Khmer
        'kik' => 'ki', // Kikuyu; Gikuyu
        'kin' => 'rw', // Kinyarwanda
        'kir' => 'ky', // Kirghiz; Kyrgyz
        'kom' => 'kv', // Komi
        'kon' => 'kg', // Kongo
        'kor' => 'ko', // Korean
        'kua' => 'kj', // Kuanyama; Kwanyama
        'kur' => 'ku', // Kurdish
        'lao' => 'lo', // Lao
        'lat' => 'la', // Latin
        'lav' => 'lv', // Latvian
        'lim' => 'li', // Limburgan; Limburger; Limburgish
        'lin' => 'ln', // Lingala
        'lit' => 'lt', // Lithuanian
        'ltz' => 'lb', // Luxembourgish; Letzeburgesch
        'lub' => 'lu', // Luba-Katanga
        'lug' => 'lg', // Ganda
        'mac' => 'mk', // Macedonian
        'mah' => 'mh', // Marshallese
        'mal' => 'ml', // Malayalam
        'mao' => 'mi', // Maori
        'mar' => 'mr', // Marathi
        'may' => 'ms', // Malay
        'mlg' => 'mg', // Malagasy
        'mlt' => 'mt', // Maltese
        'mon' => 'mn', // Mongolian
        'nau' => 'na', // Nauru
        'nav' => 'nv', // Navajo; Navaho
        'nbl' => 'nr', // Ndebele, South; South Ndebele
        'nde' => 'nd', // Ndebele, North; North Ndebele
        'ndo' => 'ng', // Ndonga
        'nep' => 'ne', // Nepali
        'nno' => 'nn', // Norwegian Nynorsk; Nynorsk, Norwegian
        'nob' => 'nb', // Bokm?l, Norwegian; Norwegian Bokm?l
        'nor' => 'no', // Norwegian
        'nya' => 'ny', // Chichewa; Chewa; Nyanja
        'oci' => 'oc', // Occitan (post 1500)
        'oji' => 'oj', // Ojibwa
        'ori' => 'or', // Oriya
        'orm' => 'om', // Oromo
        'oss' => 'os', // Ossetian; Ossetic
        'pan' => 'pa', // Panjabi; Punjabi
        'per' => 'fa', // Persian
        'pli' => 'pi', // Pali
        'pol' => 'pl', // Polish
        'por' => 'pt', // Portuguese
        'pus' => 'ps', // Pushto; Pashto
        'que' => 'qu', // Quechua
        'roh' => 'rm', // Romansh
        'rum' => 'ro', // Romanian; Moldavian; Moldovan
        'run' => 'rn', // Rundi
        'rus' => 'ru', // Russian
        'sag' => 'sg', // Sango
        'san' => 'sa', // Sanskrit
        'sin' => 'si', // Sinhala; Sinhalese
        'slo' => 'sk', // Slovak
        'slv' => 'sl', // Slovenian
        'sme' => 'se', // Northern Sami
        'smo' => 'sm', // Samoan
        'sna' => 'sn', // Shona
        'snd' => 'sd', // Sindhi
        'som' => 'so', // Somali
        'sot' => 'st', // Sotho, Southern
        'spa' => 'es', // Spanish; Castilian
        'srd' => 'sc', // Sardinian
        'srp' => 'sr', // Serbian
        'ssw' => 'ss', // Swati
        'sun' => 'su', // Sundanese
        'swa' => 'sw', // Swahili
        'swe' => 'sv', // Swedish
        'tah' => 'ty', // Tahitian
        'tam' => 'ta', // Tamil
        'tat' => 'tt', // Tatar
        'tel' => 'te', // Telugu
        'tgk' => 'tg', // Tajik
        'tgl' => 'tl', // Tagalog
        'tha' => 'th', // Thai
        'tib' => 'bo', // Tibetan
        'tir' => 'ti', // Tigrinya
        'ton' => 'to', // Tonga (Tonga Islands)
        'tsn' => 'tn', // Tswana
        'tso' => 'ts', // Tsonga
        'tuk' => 'tk', // Turkmen
        'tur' => 'tr', // Turkish
        'twi' => 'tw', // Twi
        'uig' => 'ug', // Uighur; Uyghur
        'ukr' => 'uk', // Ukrainian
        'urd' => 'ur', // Urdu
        'uzb' => 'uz', // Uzbek
        'ven' => 've', // Venda
        'vie' => 'vi', // Vietnamese
        'vol' => 'vo', // Volapük
        'wel' => 'cy', // Welsh
        'wln' => 'wa', // Walloon
        'wol' => 'wo', // Wolof
        'xho' => 'xh', // Xhosa
        'yid' => 'yi', // Yiddish
        'yor' => 'yo', // Yoruba
        'zha' => 'za', // Zhuang; Chuang
        'zul' => 'zu'
    ];

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle)
    {
        return \mb_strpos($haystack, $needle) === 0;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle)
    {
        $length = \mb_strlen($needle);
        return \mb_substr($haystack, -$length, $length) === $needle;
    }

    /**
     * @param string $input
     * @param int    $flags
     * @param string $enc
     * @return string
     */
    public static function htmlentities(string $input, int $flags = \ENT_COMPAT, string $enc = \JTL_CHARSET): string
    {
        return \htmlentities($input, $flags, $enc);
    }

    /**
     * @param string $input
     * @param int    $flags
     * @param string $enc
     * @return string
     * @since 5.0.0
     */
    public static function htmlentitiesOnce(
        string $input,
        int $flags = \ENT_COMPAT,
        string $enc = \JTL_CHARSET
    ): string {
        return \htmlentities($input, $flags, $enc, false);
    }

    /**
     * @param string $input
     * @param int    $length
     * @return string
     * @since 5.0.0
     */
    public static function htmlentitiesSubstr(string $input, int $length): string
    {
        if ($length > 0 && \mb_strlen($input) > $length) {
            $regex = '/(&#x?[0-9a-f]+;)|(&\w{2,8};)|(\e)/i';
            if (\preg_match_all($regex, $input, $hits)) {
                // set escape-sequence as placeholder for html entities
                $input = \preg_replace($regex, \chr(27), $input);
            }
            $input = \mb_substr($input, 0, $length);
            if (\count($hits[0]) > 0) {
                // reset placeholder to preserved html entities
                $input = \vsprintf(\str_replace(['%', \chr(27)], ['%%', '%s'], $input), $hits[0]);
            }
        }

        return $input;
    }

    /**
     * @param string $input
     * @return string
     */
    public static function unhtmlentities($input): string
    {
        // replace numeric entities
        $input = \preg_replace_callback(
            '~&#x([0-9a-fA-F]+);~i',
            static function ($x) {
                return \chr(\hexdec($x[1]));
            },
            $input
        );

        return self::htmlentitydecode(\preg_replace_callback(
            '~&#([0-9]+);~',
            static function ($x) {
                return \chr((int)$x[1]);
            },
            $input
        ));
    }

    /**
     * @param string $input
     * @param int    $flags
     * @param string $enc
     * @return string
     */
    public static function htmlspecialchars(
        string $input,
        int $flags = \ENT_COMPAT,
        string $enc = \JTL_CHARSET
    ): string {
        return \htmlspecialchars($input, $flags, $enc);
    }

    /**
     * @param string $input
     * @param int    $flags
     * @param string $enc
     * @return string
     */
    public static function htmlentitydecode(
        string $input,
        int $flags = \ENT_COMPAT,
        string $enc = \JTL_CHARSET
    ): string {
        return \html_entity_decode($input, $flags, $enc);
    }

    /**
     * @param int    $flags
     * @param string $enc
     * @return array
     */
    public static function gethtmltranslationtable(int $flags = \ENT_QUOTES, string $enc = \JTL_CHARSET): array
    {
        return \get_html_translation_table(\HTML_ENTITIES, $flags, $enc);
    }

    /**
     * @param string|array $input
     * @param int          $search
     * @return array|string|mixed
     */
    public static function filterXSS($input, int $search = 0)
    {
        if (\is_array($input)) {
            foreach ($input as &$a) {
                $a = self::filterXSS($a);
            }

            return $input;
        }
        $input  = (string)$input;
        $string = \trim(\strip_tags($input));
        $string = $search === 1
            ? \str_replace(['\\\'', '\\'], '', $string)
            : \str_replace(['\"', '\\\'', '\\', '"', '\''], '', $string);

        if ($search === 1 && \mb_strlen($string) > 10) {
            $string = \mb_substr(\str_replace(['(', ')', ';'], '', $string), 0, 50);
        }

        return $string;
    }

    /**
     * check if string already is utf8 encoded
     * @source http://w3.org/International/questions/qa-forms-utf-8.html
     * @param string $input
     * @return int
     */
    public static function is_utf8(string $input)
    {
        $res = \preg_match(
            '%^(?:[\x09\x0A\x0D\x20-\x7E]  # ASCII
                                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                            )*$%xs',
            $input
        );
        if ($res === false) {
            //some kind of pcre error happend - probably PREG_JIT_STACKLIMIT_ERROR.
            //we could check this via preg_last_error()
            $res = (int)(\mb_detect_encoding($input, 'UTF-8', true) === 'UTF-8');
        }

        return $res;
    }

    /**
     * @param string $data
     * @return mixed|string
     */
    public static function xssClean(string $data)
    {
        $convert = false;
        if (!self::is_utf8($data)) {
            //with non-utf8 input this function would return an empty string
            $convert = true;
            $data    = self::convertUTF8($data);
        }
        // Fix &entity\n;
        $data = \str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = \preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = \preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = \html_entity_decode($data, \ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = \preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = \preg_replace(
            '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]' .
            '*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]' .
            '*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...',
            $data
        );
        $data = \preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]' .
            '*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...',
            $data
        );
        $data = \preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...',
            $data
        );
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = \preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = \preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = \preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]' .
            '*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>',
            $data
        );
        // Remove namespaced elements (we do not need them)
        $data = \preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data     = \preg_replace(
                '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)' .
                '?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '',
                $data
            );
        } while ($old_data !== $data);

        // we are done...
        return $convert ? self::convertISO($data) : $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function convertUTF8(string $data): string
    {
        return \mb_convert_encoding($data, 'UTF-8', \mb_detect_encoding($data, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }

    /**
     * @param string $data
     * @return string
     */
    public static function convertISO(string $data): string
    {
        return \mb_convert_encoding(
            $data,
            'ISO-8859-1',
            \mb_detect_encoding($data, 'UTF-8, ISO-8859-1, ISO-8859-15', true)
        );
    }

    /**
     * @param string $iso
     * @return string
     */
    public static function convertISO2ISO639(string $iso): string
    {
        return self::$mappings[$iso] ?? $iso;
    }

    /**
     * @param string $iso
     * @return string
     */
    public static function convertISO6392ISO(string $iso): string
    {
        return \array_search(\mb_convert_case($iso, \MB_CASE_LOWER), self::$mappings, true) ?: $iso;
    }

    /**
     * @return array
     */
    public static function getISOMappings(): array
    {
        return self::$mappings;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeDoubleSpaces(string $string): string
    {
        return \preg_replace('|  +|', ' ', \preg_quote($string, '|'));
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeWhitespace(string $string): string
    {
        return \preg_replace('/\s+/', ' ', $string);
    }

    /**
     * Creating semicolon separated key string
     *
     * @param array|mixed $keys
     * @return string
     */
    public static function createSSK($keys): string
    {
        if (!\is_array($keys) || \count($keys) === 0) {
            return '';
        }

        return \sprintf(';%s;', \implode(';', $keys));
    }

    /**
     * Parse a semicolon separated key string to an array
     *
     * @param string|mixed $ssk
     * @return array
     */
    public static function parseSSK($ssk): array
    {
        return \is_string($ssk)
            ? \array_map('\trim', \array_filter(\explode(';', $ssk)))
            : [];
    }

    /**
     * Parse a semicolon separated key string to an array
     *
     * @param string|mixed $ssk
     * @return int[]
     */
    public static function parseSSKint($ssk): array
    {
        return \is_string($ssk)
            ? \array_map(static function ($e) {
                return (int)\trim($e);
            }, \array_filter(\explode(';', $ssk), static function ($e) {
                return $e !== '' && $e !== null;
            }))
            : [];
    }

    /**
     * @note PHP's FILTER_SANITIZE_EMAIL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     * @param string $input
     * @param bool   $validate
     * @return string|bool - a filtered string or false if invalid
     */
    public static function filterEmailAddress($input, bool $validate = true)
    {
        if (!\is_string($input)) {
            return $validate ? false : '';
        }
        if (\mb_detect_encoding($input) !== 'UTF-8' || !self::is_utf8($input)) {
            $input = self::convertUTF8($input);
        }
        $input     = \idn_to_ascii($input, \IDNA_DEFAULT, \INTL_IDNA_VARIANT_UTS46);
        $sanitized = \filter_var($input, \FILTER_SANITIZE_EMAIL);

        return $validate
            ? \filter_var($sanitized, \FILTER_VALIDATE_EMAIL)
            : $sanitized;
    }

    /**
     * @note PHP's FILTER_SANITIZE_URL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     * @param string $input
     * @param bool   $validate
     * @return string|false - a filtered string or false if invalid
     */
    public static function filterURL($input, bool $validate = true)
    {
        if (\mb_detect_encoding($input) !== 'UTF-8' || !self::is_utf8($input)) {
            $input = self::convertUTF8($input);
        }
        $input     = \idn_to_ascii($input, \IDNA_DEFAULT, \INTL_IDNA_VARIANT_UTS46);
        $sanitized = \filter_var($input, \FILTER_SANITIZE_URL);

        return $validate
            ? \filter_var($sanitized, \FILTER_VALIDATE_URL)
            : $sanitized;
    }

    /**
     * Build an URL string from a given associative array of parts according to PHP's \parse_url()
     *
     * @param array $parts
     * @return string - the resulting URL
     */
    public static function buildUrl(array $parts): string
    {
        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
            (isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '') .
            ($parts['host'] ?? '') .
            (isset($parts['port']) ? ':' . $parts['port'] : '') .
            ($parts['path'] ?? '') .
            (isset($parts['query']) ? '?' . $parts['query'] : '') .
            (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * @param string $number
     * @param bool   $required
     * @return int
     * @former checkeTel()
     */
    public static function checkPhoneNumber(string $number, bool $required = true): int
    {
        if (!$number) {
            if ($required) {
                return 1;
            }

            return 0;
        }
        if (!\preg_match('/^[0-9\-\(\)\/\+\s]{1,}$/', $number)) {
            return 2;
        }

        return 0;
    }

    /**
     * @param string $data
     * @param bool   $required
     * @return int
     */
    public static function checkDate(string $data, bool $required = true): int
    {
        if (!$data) {
            return $required ? 1 : 0;
        }
        if (!\preg_match('/^\d{1,2}\.\d{1,2}\.(\d{4})$/', $data)) {
            return 2;
        }
        [$tag, $monat, $jahr] = \explode('.', $data);

        return !\checkdate($monat, $tag, $jahr) ? 3 : 0;
    }

    /**
     * @param int    $size
     * @param string $format
     * @return string
     */
    public static function formatSize($size, string $format = '%.2f'): string
    {
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
        $res   = '';
        foreach ($units as $n => $unit) {
            $div = 1024 ** $n;
            if ($size > $div) {
                $res = \sprintf("$format %s", $size / $div, $unit);
            }
        }

        return $res;
    }

    /**
     * @param string|array|object $data   the string, array or object to convert recursively
     * @param bool                $encode true if data should be utf-8-encoded or false if data should be utf-8-decoded
     * @param bool                $copy   false if objects should be changed, true if they should be cloned first
     * @return string|array|object converted data
     */
    public static function utf8_convert_recursive($data, $encode = true, $copy = false)
    {
        if (\is_string($data)) {
            $isUtf8 = \mb_detect_encoding($data, 'UTF-8', true) !== false;
            if ((!$isUtf8 && $encode) || ($isUtf8 && !$encode)) {
                $data = $encode ? self::convertUTF8($data) : self::convertISO($data);
            }
        } elseif (\is_array($data)) {
            foreach ($data as $key => $val) {
                $newKey = (string)self::utf8_convert_recursive($key, $encode);
                $newVal = self::utf8_convert_recursive($val, $encode);
                unset($data[$key]);
                $data[$newKey] = $newVal;
            }
        } elseif (\is_object($data)) {
            if ($copy) {
                $data = clone $data;
            }
            foreach (\get_object_vars($data) as $key => $val) {
                $newKey = (string)self::utf8_convert_recursive($key, $encode);
                $newVal = self::utf8_convert_recursive($val, $encode);
                unset($data->$key);
                $data->$newKey = $newVal;
            }
        }

        return $data;
    }

    /**
     * JSON-Encode $data only if it is not already encoded, meaning it avoids double encoding
     *
     * @param mixed $data
     * @return string|bool - false when $data is not encodable
     * @throws Exception
     */
    public static function json_safe_encode($data)
    {
        $data = self::utf8_convert_recursive($data);
        // encode data if not already encoded
        if (\is_string($data)) {
            // data is a string
            \json_decode($data);
            if (\json_last_error() !== \JSON_ERROR_NONE) {
                // it is not a JSON string yet
                $data = \json_encode($data);
            }
        } else {
            $data = \json_encode($data);
        }

        return $data;
    }

    /**
     * Mehrfach Leerzeichen entfernen
     *
     * @param string $string
     * @return string
     */
    public static function removeNumerousWhitespaces($string): string
    {
        while (\mb_strpos($string, '  ')) {
            $string = \str_replace('  ', ' ', $string);
        }

        return $string;
    }
}
