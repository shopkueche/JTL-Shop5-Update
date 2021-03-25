<?php

namespace JTL;

use JTL\Helpers\Text;

/**
 * Class XMLParser
 * @package JTL
 */
class XMLParser
{
    /**
     * @param $fileName
     * @return array
     */
    public function parse($fileName): array
    {
        $xml = \file_get_contents($fileName);

        return $this->getArrangedArray($this->unserializeXML($xml)) ?? [];
    }

    /**
     * takes raw XML as a parameter (a string)
     * and returns an equivalent PHP data structure
     *
     * @param string $xml
     * @param string $encoding
     * @return array|null
     */
    public function unserializeXML(&$xml, $encoding = 'UTF-8'): ?array
    {
        $parser = new XML($encoding);
        $data   = $parser->parse($xml);
        $parser->destruct();

        return $data;
    }

    /**
     * serializes any PHP data structure into XML
     * Takes one parameter: the data to serialize. Must be an array.
     *
     * @param mixed $data
     * @param int   $level
     * @param null  $prevKey
     * @return string
     */
    public function serializeXML(&$data, int $level = 0, $prevKey = null)
    {
        if ($level === 0) {
            \ob_start();
            echo '<?xml version="1.0" ?>', "\n";
        }
        foreach ($data as $key => $value) {
            if (!\mb_strpos($key, ' attr')) {
                if (\is_array($value) && \array_key_exists(0, $value)) {
                    $this->serializeXML($value, $level, $key);
                } else {
                    $tag = $prevKey ?: $key;
                    echo \str_repeat("\t", $level), '<', $tag;
                    if (\array_key_exists($key . ' attr', $data)) { // if there's an attribute for this element
                        foreach ($data[$key . ' attr'] as $attr_name => $attr_value) {
                            echo ' ', $attr_name, '="', Text::htmlspecialchars($attr_value), '"';
                        }
                        \reset($data[$key . ' attr']);
                    }

                    if ($value === null) {
                        echo " />\n";
                    } elseif (!\is_array($value)) {
                        echo '>', Text::htmlspecialchars($value), '</' . $tag . ">\n";
                    } else {
                        echo ">\n", $this->serializeXML($value, $level + 1),
                        \str_repeat("\t", $level), '</' . $tag . ">\n";
                    }
                }
            }
        }
        \reset($data);
        if ($level === 0) {
            $str = \ob_get_contents();
            \ob_end_clean();

            return $str;
        }
    }

    /**
     * @param array|mixed $xml
     * @param int         $level
     * @return array|mixed
     */
    public function getArrangedArray($xml, int $level = 1)
    {
        if (!\is_array($xml)) {
            return $xml;
        }
        $keys  = \array_keys($xml);
        $count = \count($xml);
        for ($i = 0; $i < $count; $i++) {
            if (\mb_strpos((string)$keys[$i], ' attr') !== false) {
                // attribut array -> nicht beachten -> weiter
                continue;
            }
            if ($level === 0 || (int)$keys[$i] > 0 || $keys[$i] == '0') {
                // int Arrayelement -> in die Tiefe gehen
                $xml[$keys[$i]] = $this->getArrangedArray($xml[$keys[$i]]);
            } elseif (isset($xml[$keys[$i]][0])) {
                $xml[$keys[$i]] = $this->getArrangedArray($xml[$keys[$i]]);
            } else {
                if ($xml[$keys[$i]] === '') {
                    continue;
                }
                // kein Attributzweig, kein numerischer Anfang
                $tmp           = [];
                $tmp['0 attr'] = $xml[$keys[$i] . ' attr'] ?? null;
                $tmp['0']      = $xml[$keys[$i]];
                unset($xml[$keys[$i]], $xml[$keys[$i] . ' attr']);
                $xml[$keys[$i]] = $tmp;
                if (\is_array($xml[$keys[$i]]['0'])) {
                    $xml[$keys[$i]]['0'] = $this->getArrangedArray($xml[$keys[$i]]['0']);
                }
            }
        }

        return $xml;
    }
}
