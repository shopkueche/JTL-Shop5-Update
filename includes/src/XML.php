<?php

namespace JTL;

/**
 * Class XML
 * @package JTL
 */
class XML
{
    /**
     * @var resource
     */
    public $parser;

    /**
     * @var
     */
    public $document;

    /**
     * @var
     */
    public $parent;

    /**
     * @var
     */
    public $stack;

    /**
     * @var string|null
     */
    public $last_opened_tag;

    /**
     * @var string
     */
    public $data;

    /**
     * XML constructor.
     * @param string $encoding
     */
    public function __construct($encoding)
    {
        $this->parser = \xml_parser_create($encoding);
        \xml_parser_set_option($this->parser, \XML_OPTION_CASE_FOLDING, false);
        \xml_parser_set_option($this->parser, \XML_OPTION_TARGET_ENCODING, \JTL_CHARSET);

        \xml_set_object($this->parser, $this);
        \xml_set_element_handler($this->parser, 'open', 'close');
        \xml_set_character_data_handler($this->parser, 'data');
    }

    /**
     *
     */
    public function destruct(): void
    {
        \xml_parser_free($this->parser);
    }

    /**
     * takes raw XML as a parameter (a string)
     * and returns an equivalent PHP data structure
     *
     * @param string $xml
     * @param string $encoding
     * @return array|null
     */
    public static function unserialize(&$xml, $encoding = 'UTF-8'): ?array
    {
        $parser = new self($encoding);
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
    public static function serialize($data, $level = 0, $prevKey = null)
    {
        $parser = new XMLParser();

        return $parser->serializeXML($data, $level, $prevKey);
    }

    /**
     * @param mixed $data
     * @return array|null
     */
    public function parse(&$data)
    {
        $this->document = [];
        $this->stack    = [];
        $this->parent   = &$this->document;

        return \xml_parse($this->parser, $data, true) ? $this->document : null;
    }

    /**
     * @param resource $parser
     * @param mixed    $tag
     * @param mixed    $attributes
     */
    public function open($parser, $tag, $attributes): void
    {
        $this->data            = '';
        $this->last_opened_tag = $tag;
        if (\is_array($this->parent) && \array_key_exists($tag, $this->parent)) {
            if (\is_array($this->parent[$tag]) && \array_key_exists(0, $this->parent[$tag])) {
                $key = $this->countNumericItems($this->parent[$tag]);
            } else {
                if (\array_key_exists("$tag attr", $this->parent)) {
                    $arr = ['0 attr' => &$this->parent["$tag attr"], &$this->parent[$tag]];
                    unset($this->parent["$tag attr"]);
                } else {
                    $arr = [&$this->parent[$tag]];
                }
                $this->parent[$tag] = &$arr;
                $key                = 1;
            }
            $this->parent = &$this->parent[$tag];
        } else {
            $key = $tag;
        }

        if ($attributes) {
            $this->parent["$key attr"] = $attributes;
        }
        $this->parent  = &$this->parent[$key];
        $this->stack[] = &$this->parent;
    }

    /**
     * @param resource $parser
     * @param string   $data
     */
    public function data($parser, $data): void
    {
        if ($this->last_opened_tag !== null) {
            $this->data .= $data;
        }
    }

    /**
     * @param resource $parser
     * @param string   $tag
     */
    public function close($parser, $tag): void
    {
        if ($this->last_opened_tag === $tag) {
            $this->parent          = $this->data;
            $this->last_opened_tag = null;
        }
        \array_pop($this->stack);
        if ($this->stack) {
            $this->parent = &$this->stack[\count($this->stack) - 1];
        }
    }

    /**
     * @param array $array
     * @return int
     */
    private function countNumericItems(&$array): int
    {
        return \is_array($array) ? \count(\array_filter(\array_keys($array), '\is_numeric')) : 0;
    }
}
