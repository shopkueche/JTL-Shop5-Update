<?php

namespace JTL\phpQuery;

use ArrayAccess;
use Closure;
use Countable;
use DOMDocument;
use JTL\phpQuery\DOMDocumentWrapper;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Exception;
use Iterator;
use JTL\Helpers\Text;

/**
 * Class representing phpQuery objects.
 *
 * Class phpQueryObject
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package JTL\phpQuery
 * @method self clone()
 */
class phpQueryObject implements Iterator, Countable, ArrayAccess
{
    /**
     * @var
     */
    public $_loadSelector;

    /**
     * @var string|null
     */
    public $documentID;

    /**
     * DOMDocument class.
     *
     * @var DOMDocument
     */
    public $document;

    /**
     * @var null
     */
    public $charset;

    /**
     * @var DOMDocumentWrapper
     */
    public $documentWrapper;

    /**
     * @var DOMXPath
     */
    public $xpath;

    /**
     * Stack of selected elements.
     *
     * @TODO refactor to ->nodes
     * @var array
     */
    public $elements = [];

    /**
     * @var array
     */
    protected $elementsBackup = [];

    /**
     * @var self|null
     */
    protected $previous;

    /**
     * @var DOMDocument|null
     */
    protected $root;

    /**
     * Indicated if doument is just a fragment (no <html> tag).
     *
     * Every document is realy a full document, so even documentFragments can
     * be queried against <html>, but getDocument(id)->htmlOuter() will return
     * only contents of <body>.
     *
     * @var bool
     */
    public $documentFragment = true;

    /**
     * @var array
     */
    protected $elementsInterator = [];

    /**
     * @var bool
     */
    protected $valid = false;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * phpQueryObject constructor.
     * @param string|phpQueryObject $documentID
     * @throws Exception
     */
    public function __construct($documentID)
    {
        $id = $documentID instanceof self
            ? $documentID->getDocumentID()
            : $documentID;
        if (!isset(phpQuery::$documents[$id])) {
            throw new Exception("Document with ID '{$id}' isn't loaded. 
            Use phpQuery::newDocument(\$html) or phpQuery::newDocumentFile(\$file) first.");
        }
        $this->documentID       = $id;
        $this->documentWrapper  =& phpQuery::$documents[$id];
        $this->document         =& $this->documentWrapper->document;
        $this->xpath            =& $this->documentWrapper->xpath;
        $this->charset          =& $this->documentWrapper->charset;
        $this->documentFragment =& $this->documentWrapper->isDocumentFragment;
        $this->root             =& $this->documentWrapper->root;
        $this->elements         = [$this->root];
    }

    /**
     * @param string $attr
     * @return mixed
     */
    public function __get($attr)
    {
        return $attr === 'length' ? $this->size() : $this->$attr;
    }

    /**
     * Saves actual object to $var by reference.
     * Useful when need to break chain.
     *
     * @param phpQueryObject $var
     * @return phpQueryObject
     */
    public function toReference(&$var): self
    {
        return $var = $this;
    }

    /**
     * @param null $state
     * @return $this|bool
     */
    public function documentFragment($state = null)
    {
        if ($state) {
            phpQuery::$documents[$this->getDocumentID()]['documentFragment'] = $state;

            return $this;
        }

        return $this->documentFragment;
    }

    /**
     * @param DOMDocument|DOMElement $node
     * @return bool
     */
    protected function isRoot($node): bool
    {
        return $node instanceof DOMDocument
            || ($node instanceof DOMElement && $node->tagName === 'html')
            || $this->root->isSameNode($node);
    }

    /**
     * @return bool
     */
    protected function stackIsRoot(): bool
    {
        return $this->size() === 1 && $this->isRoot($this->elements[0]);
    }

    /**
     * Watch out, it doesn't creates new instance, can be reverted with end().
     * @return phpQueryObject
     */
    public function toRoot(): self
    {
        $this->elements = [$this->root];

        return $this;
    }

    /**
     * Saves object's DocumentID to $var by reference.
     * <code>
     * $myDocumentId;
     * phpQuery::newDocument('<div/>')
     *     ->getDocumentIDRef($myDocumentId)
     *     ->find('div')->...
     * </code>
     *
     * @param $documentID
     * @return phpQueryObject
     * @see phpQuery::newDocumentFile
     * @see phpQuery::newDocument
     */
    public function getDocumentIDRef(&$documentID): self
    {
        $documentID = $this->getDocumentID();

        return $this;
    }

    /**
     * Returns object with stack set to document root.
     *
     * @return phpQueryObject
     */
    public function getDocument(): phpQueryObject
    {
        return phpQuery::getDocument($this->getDocumentID());
    }

    /**
     * @return DOMDocument
     */
    public function getDOMDocument()
    {
        return $this->document;
    }

    /**
     * Get object's Document ID.
     *
     * @return string
     */
    public function getDocumentID(): string
    {
        return $this->documentID;
    }

    /**
     * Unloads whole document from memory.
     * CAUTION! None further operations will be possible on this document.
     * All objects refering to it will be useless.
     */
    public function unloadDocument(): void
    {
        phpQuery::unloadDocuments($this->getDocumentID());
    }

    /**
     * @return bool
     */
    public function isHTML(): bool
    {
        return $this->documentWrapper->isHTML;
    }

    /**
     * @return bool
     */
    public function isXHTML(): bool
    {
        return $this->documentWrapper->isXHTML;
    }

    /**
     * @return bool
     */
    public function isXML(): bool
    {
        return $this->documentWrapper->isXML;
    }

    /**
     * @link http://docs.jquery.com/Ajax/serialize
     * @return string
     * @throws Exception
     */
    public function serialize(): string
    {
        return phpQuery::param($this->serializeArray());
    }

    /**
     * @link http://docs.jquery.com/Ajax/serializeArray
     * @param null|string|DOMElement $submit
     * @return array
     * @throws Exception
     */
    public function serializeArray($submit = null): array
    {
        $source = $this->filter('form, input, select, textarea')
            ->find('input, select, textarea')
            ->andSelf()
            ->not('form');
        $return = [];
        foreach ($source as $input) {
            $input = phpQuery::pq($input);
            if ($input->is('[disabled]')) {
                continue;
            }
            if (!$input->is('[name]')) {
                continue;
            }
            if ($input->is('[type=checkbox]') && !$input->is('[checked]')) {
                continue;
            }
            // jquery diff
            if ($submit && $input->is('[type=submit]')) {
                if ($submit instanceof DOMElement && !$input->elements[0]->isSameNode($submit)) {
                    continue;
                }
                if (\is_string($submit) && $input->attr('name') !== $submit) {
                    continue;
                }
            }
            $return[] = [
                'name'  => $input->attr('name'),
                'value' => $input->val(),
            ];
        }

        return $return;
    }

    /**
     * @param mixed $in
     */
    protected function debug($in)
    {
        if (!phpQuery::$debug) {
            return;
        }
        print('<pre>');
        \print_r($in);
        print("</pre>\n");
    }

    /**
     * @param string $pattern
     * @return bool
     */
    protected function isRegexp($pattern): bool
    {
        return \in_array(
            $pattern[\mb_strlen($pattern) - 1],
            ['^', '*', '$'],
            true
        );
    }

    /**
     * Determines if $char is really a char.
     *
     * @param string $char
     * @return bool|int
     */
    protected function isChar($char)
    {
        return \preg_match('@\w@', $char);
    }

    /**
     * @param string $query
     * @return array
     */
    protected function parseSelector($query): array
    {
        // clean spaces
        // TODO include this inside parsing ?
        $query   = \trim(
            \preg_replace(
                '@\s+@',
                ' ',
                \preg_replace('@\s*(>|\\+|~)\s*@', '\\1', $query)
            )
        );
        $queries = [[]];
        if (!$query) {
            return $queries;
        }
        $return              =& $queries[0];
        $specialChars        = ['>', ' '];
        $specialCharsMapping = [];
        $strlen              = \mb_strlen($query);
        $classChars          = ['.', '-'];
        $pseudoChars         = ['-'];
        $tagChars            = ['*', '|', '-'];
        // split multibyte string
        // http://code.google.com/p/phpquery/issues/detail?id=76
        $_query = [];
        for ($i = 0; $i < $strlen; $i++) {
            $_query[] = mb_substr($query, $i, 1);
        }
        $query = $_query;
        // it works, but i dont like it...
        $i = 0;
        while ($i < $strlen) {
            $c   = $query[$i];
            $tmp = '';
            // TAG
            if ($this->isChar($c) || \in_array($c, $tagChars, true)) {
                while (isset($query[$i])
                    && ($this->isChar($query[$i]) || \in_array($query[$i], $tagChars, true))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $return[] = $tmp;
                // IDs
            } elseif ($c === '#') {
                $i++;
                while (isset($query[$i]) && ($this->isChar($query[$i]) || $query[$i] === '-')) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $return[] = '#' . $tmp;
                // SPECIAL CHARS
            } elseif (\in_array($c, $specialChars, true)) {
                $return[] = $c;
                $i++;
                // MAPPED SPECIAL MULTICHARS
            } elseif (isset($specialCharsMapping[$c])) {
                $return[] = $specialCharsMapping[$c];
                $i++;
                // COMMA
            } elseif ($c === ',') {
                $queries[] = [];
                $return    =& $queries[count($queries) - 1];
                $i++;
                while (isset($query[$i]) && $query[$i] === ' ') {
                    $i++;
                }
                // CLASSES
            } elseif ($c === '.') {
                while (isset($query[$i]) && ($this->isChar($query[$i]) || \in_array(
                    $query[$i],
                    $classChars,
                    true
                ))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $return[] = $tmp;
                // ~ General Sibling Selector
            } elseif ($c === '~') {
                $spaceAllowed = true;
                $tmp         .= $query[$i++];
                while (isset($query[$i])
                    && ($this->isChar($query[$i])
                        || \in_array($query[$i], $classChars, true)
                        || $query[$i] === '*'
                        || ($query[$i] === ' ' && $spaceAllowed)
                    )) {
                    if ($query[$i] !== ' ') {
                        $spaceAllowed = false;
                    }
                    $tmp .= $query[$i];
                    $i++;
                }
                $return[] = $tmp;
                // + Adjacent sibling selectors
            } elseif ($c === '+') {
                $spaceAllowed = true;
                $tmp         .= $query[$i++];
                while (isset($query[$i])
                    && ($this->isChar($query[$i])
                        || \in_array($query[$i], $classChars)
                        || $query[$i] === '*'
                        || ($spaceAllowed && $query[$i] === ' ')
                    )) {
                    if ($query[$i] !== ' ') {
                        $spaceAllowed = false;
                    }
                    $tmp .= $query[$i];
                    $i++;
                }
                $return[] = $tmp;
                // ATTRS
            } elseif ($c === '[') {
                $stack = 1;
                $tmp  .= $c;
                while (isset($query[++$i])) {
                    $tmp .= $query[$i];
                    if ($query[$i] === '[') {
                        $stack++;
                    } elseif ($query[$i] === ']') {
                        $stack--;
                        if (!$stack) {
                            break;
                        }
                    }
                }
                $return[] = $tmp;
                $i++;
                // PSEUDO CLASSES
            } elseif ($c === ':') {
                $tmp .= $query[$i++];
                while (isset($query[$i]) && ($this->isChar($query[$i]) || \in_array(
                    $query[$i],
                    $pseudoChars,
                    true
                ))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                // with arguments ?
                if (isset($query[$i]) && $query[$i] === '(') {
                    $tmp  .= $query[$i];
                    $stack = 1;
                    while (isset($query[++$i])) {
                        $tmp .= $query[$i];
                        if ($query[$i] === '(') {
                            $stack++;
                        } elseif ($query[$i] === ')') {
                            $stack--;
                            if (!$stack) {
                                break;
                            }
                        }
                    }
                    $return[] = $tmp;
                    $i++;
                } else {
                    $return[] = $tmp;
                }
            } else {
                $i++;
            }
        }
        foreach ($queries as $k => $q) {
            if (isset($q[0])) {
                if (isset($q[0][0]) && $q[0][0] === ':') {
                    \array_unshift($queries[$k], '*');
                }
                if ($q[0] !== '>') {
                    \array_unshift($queries[$k], ' ');
                }
            }
        }

        return $queries;
    }

    /**
     * Return matched DOM nodes.
     *
     * @param null $index
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return array|bool|mixed|null
     */
    public function get($index = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        $return = $index !== null
            ? ($this->elements[$index] ?? null)
            : $this->elements;
        // pass thou callbacks
        $args = \func_get_args();
        $args = \array_slice($args, 1);
        foreach ($args as $callback) {
            if (\is_array($return)) {
                foreach ($return as $k => $v) {
                    $return[$k] = phpQuery::callbackRun($callback, [$v]);
                }
            } else {
                $return = phpQuery::callbackRun($callback, [$return]);
            }
        }

        return $return;
    }

    /**
     * Return matched DOM nodes.
     * jQuery difference.
     *
     * @param int $index
     * @param     $callback1
     * @param     $callback2
     * @param     $callback3
     * @return array|string Returns string if $index != null
     * @todo implement callbacks
     * @todo return only arrays ?
     * @todo maybe other name...
     */
    public function getString($index = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        if ($index) {
            $return = $this->eq($index)->text();
        } else {
            $return = [];
            for ($i = 0; $i < $this->size(); $i++) {
                $return[] = $this->eq($i)->text();
            }
        }
        // pass thou callbacks
        $args = \func_get_args();
        $args = \array_slice($args, 1);
        foreach ($args as $callback) {
            $return = phpQuery::callbackRun($callback, [$return]);
        }

        return $return;
    }

    /**
     * Return matched DOM nodes.
     * jQuery difference.
     *
     * @param int $index
     * @param     $callback1
     * @param     $callback2
     * @param     $callback3
     * @return array|string Returns string if $index != null
     * @todo implement callbacks
     * @todo return only arrays ?
     * @todo maybe other name...
     */
    public function getStrings($index = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        $args = [];
        if ($index) {
            $return = $this->eq($index)->text();
        } else {
            $return = [];
            for ($i = 0; $i < $this->size(); $i++) {
                $return[] = $this->eq($i)->text();
            }
            // pass thou callbacks
            $args = \func_get_args();
            $args = \array_slice($args, 1);
        }
        foreach ($args as $callback) {
            if (\is_array($return)) {
                foreach ($return as $k => $v) {
                    $return[$k] = phpQuery::callbackRun($callback, [$v]);
                }
            } else {
                $return = phpQuery::callbackRun($callback, [$return]);
            }
        }

        return $return;
    }

    /**
     * Returns new instance of actual class.
     *
     * @param array|string|null $newStack Optional. Will replace old stack with new and move old one to history.c
     * @return phpQueryObject
     * @throws Exception
     */
    public function newInstance($newStack = null)
    {
        $class = \get_class($this);
        // support inheritance by passing old object to overloaded constructor
        $new           = $class !== __CLASS__
            ? new $class($this, $this->getDocumentID())
            : new self($this->getDocumentID());
        $new->previous = $this;
        if ($newStack === null) {
            $new->elements = $this->elements;
            if ($this->elementsBackup) {
                $this->elements = $this->elementsBackup;
            }
        } elseif (\is_string($newStack)) {
            $new->elements = phpQuery::pq($newStack, $this->getDocumentID())->stack();
        } else {
            $new->elements = $newStack;
        }

        return $new;
    }

    /**
     * In the future, when PHP will support XLS 2.0, then we would do that this way:
     * contains(tokenize(@class, '\s'), "something")
     *
     * @param string     $class
     * @param DOMElement $node
     * @return bool
     */
    protected function matchClasses($class, $node): bool
    {
        // multi-class
        if (\mb_strpos($class, '.', 1)) {
            $classes          = \explode('.', \substr($class, 1));
            $classesCount     = \count($classes);
            $nodeClasses      = \explode(' ', $node->getAttribute('class'));
            $nodeClassesCount = \count($nodeClasses);
            if ($classesCount > $nodeClassesCount) {
                return false;
            }
            $diff = \count(
                \array_diff(
                    $classes,
                    $nodeClasses
                )
            );
            return !$diff;
        }
        return \in_array(
        // strip leading dot from class name
            \substr($class, 1),
            // get classes for element as array
            \explode(' ', $node->getAttribute('class')),
            true
        );
    }

    /**
     * @param string      $xQuery
     * @param string|null $selector
     * @param string|null $compare
     * @return void
     */
    protected function runQuery($xQuery, $selector = null, $compare = null): void
    {
        if ($compare && !\method_exists($this, $compare)) {
            return;
        }
        $stack = [];
        // element, document
        foreach ($this->stack([1, 9, 13]) as $k => $stackNode) {
            $detachAfter = false;
            // to work on detached nodes we need temporary place them somewhere
            // thats because context xpath queries sucks ;]
            $testNode = $stackNode;
            while ($testNode) {
                if (!$testNode->parentNode && !$this->isRoot($testNode)) {
                    $this->root->appendChild($testNode);
                    $detachAfter = $testNode;
                    break;
                }
                $testNode = $testNode->parentNode ?? null;
            }
            $xpath = $this->documentWrapper->isXHTML
                ? $this->getNodeXpath($stackNode, 'html')
                : $this->getNodeXpath($stackNode);
            $query = $xQuery === '//' && $xpath === '/html[1]'
                ? '//*'
                : $xpath . $xQuery;
            // run query, get elements
            $nodes = $this->xpath->query($query);
            $debug = [];
            foreach ($nodes as $node) {
                $matched = false;
                if ($compare) {
                    phpQuery::$debug ?
                        $this->debug('Found: ' . $this->whois($node) . ", comparing with {$compare}()")
                        : null;
                    $phpQueryDebug   = phpQuery::$debug;
                    phpQuery::$debug = false;
                    if ($this->$compare($selector, $node)) {
                        $matched = true;
                    }
                    phpQuery::$debug = $phpQueryDebug;
                } else {
                    $matched = true;
                }
                if ($matched) {
                    if (phpQuery::$debug) {
                        $debug[] = $this->whois($node);
                    }
                    $stack[] = $node;
                }
            }
            if (phpQuery::$debug) {
                $this->debug('Matched ' . count($debug) . ': ' . \implode(', ', $debug));
            }
            if ($detachAfter) {
                $this->root->removeChild($detachAfter);
            }
        }
        $this->elements = $stack;
    }

    /**
     * @param      $selectors
     * @param null $context
     * @param bool $noHistory
     * @return phpQueryObject
     * @throws Exception
     */
    public function find($selectors, $context = null, $noHistory = false)
    {
        if (!$noHistory) {
            // backup last stack /for end()/
            $this->elementsBackup = $this->elements;
        }
        // allow to define context
        // TODO combine code below with phpQuery::pq() context guessing code
        //   as generic function
        if ($context) {
            if (!\is_array($context) && $context instanceof DOMElement) {
                $this->elements = [$context];
            } elseif (\is_array($context)) {
                $this->elements = [];
                foreach ($context as $c) {
                    if ($c instanceof DOMElement) {
                        $this->elements[] = $c;
                    }
                }
            } elseif ($context instanceof self) {
                $this->elements = $context->elements;
            }
        }
        $queries = $this->parseSelector($selectors);
        $XQuery  = '';
        // remember stack state because of multi-queries
        $oldStack = $this->elements;
        // here we will be keeping found elements
        $stack = [];
        foreach ($queries as $selector) {
            $this->elements  = $oldStack;
            $delimiterBefore = false;
            foreach ($selector as $s) {
                // TAG
                $isTag = \preg_match('@^[\w|\||-]+$@', $s) || $s === '*';
                if ($isTag) {
                    if ($this->isXML()) {
                        // namespace support
                        if (mb_strpos($s, '|') !== false) {
                            $ns         = null;
                            $tag        = null;
                            [$ns, $tag] = \explode('|', $s);
                            $XQuery    .= "$ns:$tag";
                        } elseif ($s === '*') {
                            $XQuery .= '*';
                        } else {
                            $XQuery .= "*[local-name()='$s']";
                        }
                    } else {
                        $XQuery .= $s;
                    }
                    // ID
                } elseif ($s[0] === '#') {
                    if ($delimiterBefore) {
                        $XQuery .= '*';
                    }
                    $XQuery .= "[@id='" . \substr($s, 1) . "']";
                    // ATTRIBUTES
                } elseif ($s[0] === '[') {
                    if ($delimiterBefore) {
                        $XQuery .= '*';
                    }
                    // strip side brackets
                    $attr    = \trim($s, '][');
                    $execute = false;
                    // attr with specifed value
                    if (mb_strpos($s, '=')) {
                        $value          = null;
                        [$attr, $value] = \explode('=', $attr);
                        $value          = \trim($value, "'\"");
                        if ($this->isRegexp($attr)) {
                            // cut regexp character
                            $attr    = \substr($attr, 0, -1);
                            $execute = true;
                            $XQuery .= "[@{$attr}]";
                        } else {
                            $XQuery .= "[@{$attr}='{$value}']";
                        }
                        // attr without specified value
                    } else {
                        $XQuery .= "[@{$attr}]";
                    }
                    if ($execute) {
                        $this->runQuery($XQuery, $s, 'is');
                        $XQuery = '';
                        if (!$this->length) {
                            break;
                        }
                    }
                    // CLASSES
                } elseif ($s[0] === '.') {
                    // TODO use return $this->find("./self::*[contains(concat(\" \",@class,\" \"), \" $class \")]");
                    // thx wizDom ;)
                    if ($delimiterBefore) {
                        $XQuery .= '*';
                    }
                    $XQuery .= '[@class]';
                    $this->runQuery($XQuery, $s, 'matchClasses');
                    $XQuery = '';
                    if (!$this->length) {
                        break;
                    }
                    // ~ General Sibling Selector
                } elseif ($s[0] === '~') {
                    $this->runQuery($XQuery);
                    $XQuery         = '';
                    $this->elements = $this
                        ->siblings(
                            \substr($s, 1)
                        )->elements;
                    if (!$this->length) {
                        break;
                    }
                    // + Adjacent sibling selectors
                } elseif ($s[0] === '+') {
                    // TODO /following-sibling::
                    $this->runQuery($XQuery);
                    $XQuery         = '';
                    $subSelector    = \substr($s, 1);
                    $subElements    = $this->elements;
                    $this->elements = [];
                    foreach ($subElements as $node) {
                        // search first DOMElement sibling
                        $test = $node->nextSibling;
                        while ($test && !($test instanceof DOMElement)) {
                            $test = $test->nextSibling;
                        }
                        if ($test && $this->is($subSelector, $test)) {
                            $this->elements[] = $test;
                        }
                    }
                    if (!$this->length) {
                        break;
                    }
                    // PSEUDO CLASSES
                } elseif ($s[0] === ':') {
                    // TODO optimization for :first :last
                    if ($XQuery) {
                        $this->runQuery($XQuery);
                        $XQuery = '';
                    }
                    if (!$this->length) {
                        break;
                    }
                    $this->pseudoClasses($s);
                    if (!$this->length) {
                        break;
                    }
                    // DIRECT DESCENDANDS
                } elseif ($s === '>') {
                    $XQuery         .= '/';
                    $delimiterBefore = 2;
                    // ALL DESCENDANDS
                } elseif ($s === ' ') {
                    $XQuery         .= '//';
                    $delimiterBefore = 2;
                    // ERRORS
                } else {
                    phpQuery::debug("Unrecognized token '$s'");
                }
                $delimiterBefore = $delimiterBefore === 2;
            }
            // run query if any
            if ($XQuery && $XQuery !== '//') {
                $this->runQuery($XQuery);
                $XQuery = '';
            }
            foreach ($this->elements as $node) {
                if (!$this->elementsContainsNode($node, $stack)) {
                    $stack[] = $node;
                }
            }
        }
        $this->elements = $stack;

        return $this->newInstance();
    }

    /**
     * @param string $class
     */
    protected function pseudoClasses($class)
    {
        // TODO clean args parsing ?
        $args     = null;
        $class    = \ltrim($class, ':');
        $haveArgs = mb_strpos($class, '(');
        if ($haveArgs !== false) {
            $args  = \substr($class, $haveArgs + 1, -1);
            $class = \substr($class, 0, $haveArgs);
        }
        switch ($class) {
            case 'even':
            case 'odd':
                $stack = [];
                foreach ($this->elements as $i => $node) {
                    if ($class === 'even' && ($i % 2) === 0) {
                        $stack[] = $node;
                    } elseif ($class === 'odd' && $i % 2) {
                        $stack[] = $node;
                    }
                }
                $this->elements = $stack;
                break;
            case 'eq':
                $k              = (int)$args;
                $this->elements = isset($this->elements[$k])
                    ? [$this->elements[$k]]
                    : [];
                break;
            case 'gt':
                $this->elements = \array_slice($this->elements, $args + 1);
                break;
            case 'lt':
                $this->elements = \array_slice($this->elements, 0, $args + 1);
                break;
            case 'first':
                if (isset($this->elements[0])) {
                    $this->elements = [$this->elements[0]];
                }
                break;
            case 'last':
                if ($this->elements) {
                    $this->elements = [$this->elements[count($this->elements) - 1]];
                }
                break;
            case 'contains':
                $text  = \trim($args, "\"'");
                $stack = [];
                foreach ($this->elements as $node) {
                    if (mb_stripos($node->textContent, $text) === false) {
                        continue;
                    }
                    $stack[] = $node;
                }
                $this->elements = $stack;
                break;
            case 'not':
                $selector       = self::unQuote($args);
                $this->elements = $this->not($selector)->stack();
                break;
            case 'slice':
                // TODO jQuery difference ?
                $args  = \explode(
                    ',',
                    \str_replace(', ', ',', \trim($args, "\"'"))
                );
                $start = $args[0];
                $end   = $args[1] ?? null;
                if ($end > 0) {
                    $end -= $start;
                }
                $this->elements = \array_slice($this->elements, $start, $end);
                break;
            case 'has':
                $selector = \trim($args, "\"'");
                $stack    = [];
                foreach ($this->stack(1) as $el) {
                    if ($this->find($selector, $el, true)->length) {
                        $stack[] = $el;
                    }
                }
                $this->elements = $stack;
                break;
            case 'submit':
            case 'reset':
                $this->elements = phpQuery::merge(
                    $this->map(
                        [$this, 'is'],
                        "input[type=$class]",
                        new CallbackParam()
                    ),
                    $this->map(
                        [$this, 'is'],
                        "button[type=$class]",
                        new CallbackParam()
                    )
                );
                break;
            case 'input':
                $this->elements = $this->map(
                    [$this, 'is'],
                    'input',
                    new CallbackParam()
                )->elements;
                break;
            case 'password':
            case 'checkbox':
            case 'radio':
            case 'hidden':
            case 'image':
            case 'file':
                $this->elements = $this->map(
                    [$this, 'is'],
                    "input[type=$class]",
                    new CallbackParam()
                )->elements;
                break;
            case 'parent':
                $this->elements = $this->map(
                    static function ($node) {
                        return $node instanceof DOMElement && $node->childNodes->length
                            ? $node : null;
                    }
                )->elements;
                break;
            case 'empty':
                $this->elements = $this->map(
                    static function ($node) {
                        return $node instanceof DOMElement && $node->childNodes->length
                            ? null : $node;
                    }
                )->elements;
                break;
            case 'disabled':
            case 'selected':
            case 'checked':
                $this->elements = $this->map(
                    [$this, 'is'],
                    "[$class]",
                    new CallbackParam()
                )->elements;
                break;
            case 'enabled':
                $this->elements = $this->map(
                    static function ($node) {
                        return \pq($node)->not(':disabled') ? $node : null;
                    }
                )->elements;
                break;
            case 'header':
                $this->elements = $this->map(
                    static function ($node) {
                        $isHeader = isset($node->tagName)
                            && \in_array($node->tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'], true);

                        return $isHeader
                            ? $node
                            : null;
                    }
                )->elements;
                break;
            case 'only-child':
                $this->elements = $this->map(
                    static function ($node) {
                        return \pq($node)->siblings()->size() === 0 ? $node : null;
                    }
                )->elements;
                break;
            case 'first-child':
                $this->elements = $this->map(
                    static function ($node) {
                        return \pq($node)->prevAll()->size() === 0 ? $node : null;
                    }
                )->elements;
                break;
            case 'last-child':
                $this->elements = $this->map(
                    static function ($node) {
                        return \pq($node)->nextAll()->size() === 0 ? $node : null;
                    }
                )->elements;
                break;
            case 'nth-child':
                $param = \trim($args, "\"'");
                if (!$param) {
                    break;
                }
                // nth-child(n+b) to nth-child(1n+b)
                if (\strpos($param, 'n') === 0) {
                    $param = '1' . $param;
                }
                // :nth-child(index/even/odd/equation)
                if ($param === 'even' || $param === 'odd') {
                    $mapped = $this->map(
                        static function ($node, $param) {
                            $index = \pq($node)->prevAll()->size() + 1;
                            if ($param === 'even' && ($index % 2) === 0) {
                                return $node;
                            }
                            if ($param === 'odd' && ($index % 2) === 1) {
                                return $node;
                            }

                            return null;
                        },
                        new CallbackParam(),
                        $param
                    );
                } elseif (mb_strlen($param) > 1 && \strpos($param, 'n') !== false) {
                    // an+b
                    $mapped = $this->map(
                        static function ($node, $param) {
                            $prevs  = \pq($node)->prevAll()->size();
                            $index  = 1 + $prevs;
                            $bStart = \strpos($param, '+');
                            if ($bStart === false) {
                                $bStart = \strpos($param, '-');
                            }
                            $b = $bStart !== false
                                ? (int)\substr($param, $bStart)
                                : 0;
                            $a = (int)\substr($param, 0, \strpos($param, 'n'));
                            if ($a > 0) {
                                return ($index - $b) % $a === 0
                                    ? $node
                                    : null;
                            }
                            if ($a === 0) {
                                return $index == $b
                                    ? $node
                                    : null;
                            }

                            // negative value
                            return $index <= $b
                                ? $node
                                : null;
                        },
                        new CallbackParam(),
                        $param
                    );
                } else {
                    // index
                    $mapped = $this->map(
                        static function ($node, $index) {
                            $prevs = \pq($node)->prevAll()->size();
                            if ($prevs && $prevs == $index - 1) {
                                return $node;
                            }
                            if (!$prevs && $index == 1) {
                                return $node;
                            }

                            return null;
                        },
                        new CallbackParam(),
                        $param
                    );
                }
                $this->elements = $mapped->elements;
                break;
            default:
                $this->debug("Unknown pseudoclass '{$class}', skipping...");
        }
    }

    /**
     * @param string $selector
     * @param null   $nodes
     * @return array|bool|null
     */
    public function is($selector, $nodes = null)
    {
        if (!$selector) {
            return false;
        }
        $oldStack = $this->elements;
        if ($nodes && \is_array($nodes)) {
            $this->elements = $nodes;
        } elseif ($nodes) {
            $this->elements = [$nodes];
        }
        $this->filter($selector, true);
        $stack          = $this->elements;
        $this->elements = $oldStack;
        if ($nodes) {
            return $stack ?: null;
        }

        return (bool)count($stack);
    }

    /**
     * jQuery difference.
     *
     * Callback:
     * - $index int
     * - $node DOMNode
     *
     * @param callable $callback
     * @param bool     $skipHistory
     * @return phpQueryObject
     * @throws Exception
     * @link http://docs.jquery.com/Traversing/filter
     */
    public function filterCallback($callback, bool $skipHistory = false)
    {
        if (!$skipHistory) {
            $this->elementsBackup = $this->elements;
        }
        $newStack = [];
        foreach ($this->elements as $index => $node) {
            $result = phpQuery::callbackRun($callback, [$index, $node]);
            if ($result === null || ($result !== null && $result)) {
                $newStack[] = $node;
            }
        }
        $this->elements = $newStack;

        return $skipHistory
            ? $this
            : $this->newInstance();
    }

    /**
     * @param callable|Closure|array $selectors
     * @param bool                   $skipHistory
     * @return phpQueryObject
     * @throws Exception
     * @link http://docs.jquery.com/Traversing/filter
     */
    public function filter($selectors, $skipHistory = false)
    {
        if ($selectors instanceof Closure) {
            return $this->filterCallback($selectors, $skipHistory);
        }
        if (!$skipHistory) {
            $this->elementsBackup = $this->elements;
        }
        $notSimpleSelector = [' ', '>', '~', '+', '/'];
        if (!\is_array($selectors)) {
            $selectors = $this->parseSelector($selectors);
        }
        $finalStack = [];
        foreach ($selectors as $selector) {
            $stack = [];
            if (!$selector) {
                break;
            }
            // avoid first space or /
            if (\in_array($selector[0], $notSimpleSelector, true)) {
                $selector = \array_slice($selector, 1);
            }
            // PER NODE selector chunks
            foreach ($this->stack() as $node) {
                $break = false;
                foreach ($selector as $s) {
                    if (!($node instanceof DOMElement)) {
                        // all besides DOMElement
                        if ($s[0] === '[') {
                            $attr = \trim($s, '[]');
                            if (mb_strpos($attr, '=')) {
                                [$attr, $val] = \explode('=', $attr);
                                if ($attr === 'nodeType' && $node->nodeType != $val) {
                                    $break = true;
                                }
                            }
                        } else {
                            $break = true;
                        }
                    } elseif ($s[0] === '#') {
                        if ($node->getAttribute('id') !== \substr($s, 1)) {
                            $break = true;
                        }
                        // CLASSES
                    } elseif ($s[0] === '.') {
                        if (!$this->matchClasses($s, $node)) {
                            $break = true;
                        }
                        // ATTRS
                    } elseif ($s[0] === '[') {
                        // strip side brackets
                        $attr = \trim($s, '[]');
                        if (mb_strpos($attr, '=')) {
                            [$attr, $val] = \explode('=', $attr);
                            $val          = self::unQuote($val);
                            if ($attr === 'nodeType') {
                                if ($val != $node->nodeType) {
                                    $break = true;
                                }
                            } elseif ($this->isRegexp($attr)) {
                                $val = \quotemeta(\trim($val, '"\''));
                                // switch last character
                                switch (\substr($attr, -1)) {
                                    // quotemeta used insted of preg_quote
                                    // http://code.google.com/p/phpquery/issues/detail?id=76
                                    case '^':
                                        $pattern = '^' . $val;
                                        break;
                                    case '*':
                                        $pattern = '.*' . $val . '.*';
                                        break;
                                    case '$':
                                        $pattern = '.*' . $val . '$';
                                        break;
                                }
                                // cut last character
                                $attr    = \substr($attr, 0, -1);
                                $isMatch = \preg_match("@{$pattern}@", $node->getAttribute($attr));
                                if (!$isMatch) {
                                    $break = true;
                                }
                            } elseif ($node->getAttribute($attr) != $val) {
                                $break = true;
                            }
                        } elseif (!$node->hasAttribute($attr)) {
                            $break = true;
                        }
                        // PSEUDO CLASSES
                    } elseif ($s[0] === ':') {
                        // skip
                        // TAG
                    } elseif (\trim($s)) {
                        if ($s !== '*') {
                            // TODO namespaces
                            if (isset($node->tagName)) {
                                if ($node->tagName !== $s) {
                                    $break = true;
                                }
                            } elseif ($s === 'html' && !$this->isRoot($node)) {
                                $break = true;
                            }
                        }
                        // AVOID NON-SIMPLE SELECTORS
                    } elseif (\in_array($s, $notSimpleSelector, true)) {
                        $break = true;
                    }
                    if ($break) {
                        break;
                    }
                }
                // if element passed all chunks of selector - add it to new stack
                if (!$break) {
                    $stack[] = $node;
                }
            }
            $tmpStack       = $this->elements;
            $this->elements = $stack;
            // PER ALL NODES selector chunks
            foreach ($selector as $s) {// PSEUDO CLASSES
                if (\strpos($s, ':') === 0) {
                    $this->pseudoClasses($s);
                }
            }
            foreach ($this->elements as $node) {// XXX it should be merged without duplicates
                // but jQuery doesnt do that
                $finalStack[] = $node;
            }
            $this->elements = $tmpStack;
        }
        $this->elements = $finalStack;
        if ($skipHistory) {
            return $this;
        }

        return $this->newInstance();
    }

    /**
     *
     * @param string $value
     * @return string
     * @TODO implement in all methods using passed parameters
     */
    protected static function unQuote($value): string
    {
        return $value[0] === '\'' || $value[0] === '"'
            ? \substr($value, 1, -1)
            : $value;
    }

    /**
     * @param string $html
     * @throws Exception
     */
    public function __loadSuccess($html): void
    {
        if ($this->_loadSelector) {
            $html = phpQuery::newDocument($html)->find($this->_loadSelector);
            unset($this->_loadSelector);
        }
        foreach ($this->stack(1) as $node) {
            phpQuery::pq($node, $this->getDocumentID())
                ->markup($html);
        }
    }

    /**
     * @return $this
     */
    public function css(): self
    {
        // TODO
        return $this;
    }

    /**
     * @return $this
     */
    public function show(): self
    {
        // TODO
        return $this;
    }

    /**
     * @return $this
     */
    public function hide(): self
    {
        // TODO
        return $this;
    }

    /**
     * Trigger a type of event on every matched element.
     *
     * @param string $type
     * @param array  $data
     * @return phpQueryObject
     * @TODO support more than event in $type (space-separated)
     */
    public function trigger($type, $data = []): self
    {
        foreach ($this->elements as $node) {
            phpQueryEvents::trigger($this->getDocumentID(), $type, $data, $node);
        }

        return $this;
    }

    /**
     * This particular method triggers all bound event handlers on an element (for a specific event type) WITHOUT
     * executing the browsers default actions.
     *
     * @param string $type
     * @param array  $data
     */
    public function triggerHandler($type, $data = [])
    {
        // TODO;
    }

    /**
     * Binds a handler to one or more events (like click) for each matched element.
     * Can also bind custom events.
     *
     * @param $type
     * @param $data
     * @param $callback
     * @return phpQueryObject
     * @TODO support '!' (exclusive) events
     * @TODO support more than event in $type (space-separated)
     */
    public function bind($type, $data, $callback = null): self
    {
        // TODO check if $data is callable, not using is_callable
        if (!isset($callback)) {
            $callback = $data;
            $data     = null;
        }
        foreach ($this->elements as $node) {
            phpQueryEvents::add($this->getDocumentID(), $node, $type, $data, $callback);
        }

        return $this;
    }

    /**
     * @param  $type
     * @param  $callback
     * @return $this
     * @TODO namespace events
     * @TODO support more than event in $type (space-separated)
     */
    public function unbind($type = null, $callback = null): self
    {
        foreach ($this->elements as $node) {
            phpQueryEvents::remove($this->getDocumentID(), $node, $type, $callback);
        }

        return $this;
    }

    /**
     * @param null $callback
     * @return phpQueryObject
     */
    public function change($callback = null)
    {
        if ($callback) {
            return $this->bind('change', $callback);
        }

        return $this->trigger('change');
    }

    /**
     * @param null $callback
     * @return phpQueryObject
     */
    public function submit($callback = null)
    {
        if ($callback) {
            return $this->bind('submit', $callback);
        }

        return $this->trigger('submit');
    }

    /**
     * @param null $callback
     * @return phpQueryObject
     */
    public function click($callback = null)
    {
        if ($callback) {
            return $this->bind('click', $callback);
        }

        return $this->trigger('click');
    }

    /**
     * @param string $wrapper
     * @return $this
     * @throws Exception
     */
    public function wrapAllOld($wrapper): self
    {
        $wrapper = \pq($wrapper)->_clone();
        if (!$wrapper->length || !$this->length) {
            return $this;
        }
        $wrapper->insertBefore($this->elements[0]);
        $deepest = $wrapper->elements[0];
        while ($deepest->firstChild && $deepest->firstChild instanceof DOMElement) {
            $deepest = $deepest->firstChild;
        }
        \pq($deepest)->append($this);

        return $this;
    }

    /**
     * @param string $wrapper
     * @return $this
     * @throws Exception
     */
    public function wrapAll($wrapper)
    {
        if (!$this->length) {
            return $this;
        }

        return phpQuery::pq($wrapper, $this->getDocumentID())
            ->clone()
            ->insertBefore($this->get(0))
            ->map([$this, '___wrapAllCallback'])
            ->append($this);
    }

    /**
     * @param DOMElement $node
     * @return mixed
     */
    public function ___wrapAllCallback($node)
    {
        $deepest = $node;
        while ($deepest->firstChild && $deepest->firstChild instanceof DOMElement) {
            $deepest = $deepest->firstChild;
        }

        return $deepest;
    }

    /**
     * @param string $codeBefore
     * @param string $codeAfter
     * @return phpQueryObject
     * @throws Exception
     */
    public function wrapAllPHP($codeBefore, $codeAfter)
    {
        return $this->slice(0, 1)
            ->beforePHP($codeBefore)
            ->end()
            ->slice(-1)
            ->afterPHP($codeAfter)
            ->end();
    }

    /**
     * @param string $wrapper
     * @return $this
     * @throws Exception
     */
    public function wrap($wrapper): self
    {
        foreach ($this->stack() as $node) {
            phpQuery::pq($node, $this->getDocumentID())->wrapAll($wrapper);
        }

        return $this;
    }

    /**
     * @param string $codeBefore
     * @param string $codeAfter
     * @return $this
     * @throws Exception
     */
    public function wrapPHP($codeBefore, $codeAfter): self
    {
        foreach ($this->stack() as $node) {
            phpQuery::pq($node, $this->getDocumentID())->wrapAllPHP($codeBefore, $codeAfter);
        }

        return $this;
    }

    /**
     * @param string $wrapper
     * @return $this
     * @throws Exception
     */
    public function wrapInner($wrapper): self
    {
        foreach ($this->stack() as $node) {
            phpQuery::pq($node, $this->getDocumentID())->contents()->wrapAll($wrapper);
        }

        return $this;
    }

    /**
     * @param string $codeBefore
     * @param string $codeAfter
     * @return $this
     * @throws Exception
     */
    public function wrapInnerPHP($codeBefore, $codeAfter): self
    {
        foreach ($this->stack(1) as $node) {
            phpQuery::pq($node, $this->getDocumentID())->contents()
                ->wrapAllPHP($codeBefore, $codeAfter);
        }

        return $this;
    }

    /**
     * @return phpQueryObject
     * @throws Exception
     * @testme Support for text nodes
     */
    public function contents()
    {
        $stack = [];
        foreach ($this->stack(1) as $el) {
            foreach ($el->childNodes as $node) {
                $stack[] = $node;
            }
        }

        return $this->newInstance($stack);
    }

    /**
     * @return $this
     */
    public function contentsUnwrap(): self
    {
        foreach ($this->stack(1) as $node) {
            if (!$node->parentNode) {
                continue;
            }
            $childNodes = [];
            // any modification in DOM tree breaks childNodes iteration, so cache them first
            foreach ($node->childNodes as $chNode) {
                $childNodes[] = $chNode;
            }
            foreach ($childNodes as $chNode) {//                $node->parentNode->appendChild($chNode);
                $node->parentNode->insertBefore($chNode, $node);
            }
            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * @param $markup
     * @return $this
     * @throws Exception
     */
    public function switchWith($markup)
    {
        $markup  = \pq($markup, $this->getDocumentID());
        $content = null;
        foreach ($this->stack(1) as $node) {
            \pq($node)->contents()
                ->toReference($content)
                ->end()
                ->replaceWith($markup->clone()->append($content));
        }

        return $this;
    }

    /**
     * @param $num
     * @return phpQueryObject
     * @throws Exception
     */
    public function eq($num)
    {
        $oldStack             = $this->elements;
        $this->elementsBackup = $this->elements;
        $this->elements       = [];
        if (isset($oldStack[$num])) {
            $this->elements[] = $oldStack[$num];
        }

        return $this->newInstance();
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return \count($this->elements);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->size();
    }

    /**
     * @param int $level
     * @return null|phpQueryObject
     */
    public function end($level = 1)
    {
        return $this->previous ?: $this;
    }

    /**
     * Normal use ->clone() .
     *
     * @return phpQueryObject
     * @throws Exception
     */
    public function _clone()
    {
        $newStack             = [];
        $this->elementsBackup = $this->elements;
        foreach ($this->elements as $node) {
            $newStack[] = $node->cloneNode(true);
        }
        $this->elements = $newStack;

        return $this->newInstance();
    }

    /**
     * @param string $code
     * @return phpQueryObject
     * @throws Exception
     */
    public function replaceWithPHP($code)
    {
        return $this->replaceWith(phpQuery::php($code));
    }

    /**
     * @param string|phpQuery $content
     * @return phpQueryObject
     * @throws Exception
     * @link http://docs.jquery.com/Manipulation/replaceWith#content
     */
    public function replaceWith($content)
    {
        return $this->after($content)->remove();
    }

    /**
     * @param string $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function replaceAll($selector): self
    {
        foreach (phpQuery::pq($selector, $this->getDocumentID()) as $node) {
            phpQuery::pq($node, $this->getDocumentID())
                ->after($this->_clone())
                ->remove();
        }

        return $this;
    }

    /**
     * @param string|null $selector
     * @return $this
     */
    public function remove($selector = null): self
    {
        $loop = $selector
            ? $this->filter($selector)->elements
            : $this->elements;
        foreach ($loop as $node) {
            if (!$node->parentNode) {
                continue;
            }
            $node->parentNode->removeChild($node);
            // Mutation event
            $event = new DOMEvent([
                'target' => $node,
                'type'   => 'DOMNodeRemoved'
            ]);
            phpQueryEvents::trigger(
                $this->getDocumentID(),
                $event->type,
                [$event],
                $node
            );
        }

        return $this;
    }

    /**
     * @param string     $newMarkup
     * @param string     $oldMarkup
     * @param DOMElement $node
     */
    protected function markupEvents($newMarkup, $oldMarkup, $node)
    {
        if ($node->tagName === 'textarea' && $newMarkup !== $oldMarkup) {
            $event = new DOMEvent([
                'target' => $node,
                'type'   => 'change'
            ]);
            phpQueryEvents::trigger(
                $this->getDocumentID(),
                $event->type,
                [$event],
                $node
            );
        }
    }

    /**
     * jQuery difference
     *
     * @param string $markup
     * @param        $callback1
     * @param        $callback2
     * @param        $callback3
     * @return mixed
     * @TODO trigger change event for textarea
     */
    public function markup($markup = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        $args = \func_get_args();
        if ($this->documentWrapper->isXML) {
            return \call_user_func_array([$this, 'xml'], $args);
        }

        return \call_user_func_array([$this, 'html'], $args);
    }

    /**
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return mixed
     */
    public function markupOuter($callback1 = null, $callback2 = null, $callback3 = null)
    {
        $args = \func_get_args();
        if ($this->documentWrapper->isXML) {
            return \call_user_func_array([$this, 'xmlOuter'], $args);
        }

        return \call_user_func_array([$this, 'htmlOuter'], $args);
    }

    /**
     * @param null $html
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return $this|bool|mixed|string
     * @throws Exception
     */
    public function html($html = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        if (isset($html)) {
            // INSERT
            $nodes = $this->documentWrapper->import($html);
            $this->_empty();
            foreach ($this->stack(1) as $alreadyAdded => $node) {
                // for now, limit events for textarea
                if ($node->tagName === 'textarea' && ($this->isXHTML() || $this->isHTML())) {
                    $oldHtml = \pq($node, $this->getDocumentID())->markup();
                }
                foreach ($nodes as $newNode) {
                    $node->appendChild(
                        $alreadyAdded
                            ? $newNode->cloneNode(true)
                            : $newNode
                    );
                }
                // for now, limit events for textarea
                if ($node->tagName === 'textarea' && ($this->isXHTML() || $this->isHTML())) {
                    $this->markupEvents($html, $oldHtml, $node);
                }
            }

            return $this;
        }
        // FETCH
        $return = $this->documentWrapper->markup($this->elements, true);
        $args   = \func_get_args();
        foreach (\array_slice($args, 1) as $callback) {
            $return = phpQuery::callbackRun($callback, [$return]);
        }

        return $return;
    }

    /**
     * @param null $xml
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return mixed
     * @TODO force xml result
     */
    public function xml($xml = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        return \call_user_func_array([$this, 'html'], \func_get_args());
    }

    /**
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return string
     * @TODO force html result
     */
    public function htmlOuter($callback1 = null, $callback2 = null, $callback3 = null): string
    {
        $markup = $this->documentWrapper->markup($this->elements);
        // pass thou callbacks
        foreach (\func_get_args() as $callback) {
            $markup = phpQuery::callbackRun($callback, [$markup]);
        }

        return $markup;
    }

    /**
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return mixed
     * @TODO force xml result
     */
    public function xmlOuter($callback1 = null, $callback2 = null, $callback3 = null)
    {
        return \call_user_func_array([$this, 'htmlOuter'], \func_get_args());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->markupOuter();
    }

    /**
     * Just like html(), but returns markup with VALID (dangerous) PHP tags.
     *
     * @param $code
     * @return phpQueryObject
     * @todo support returning markup with PHP tags when called without param
     */
    public function php($code = null)
    {
        return $this->markupPHP($code);
    }

    /**
     * @param null $code
     * @return string|mixed
     */
    public function markupPHP($code = null)
    {
        return $code !== null
            ? $this->markup(phpQuery::php($code))
            : phpQuery::markupToPHP($this->markup());
    }

    /**
     * @return string
     */
    public function markupOuterPHP(): string
    {
        return phpQuery::markupToPHP($this->markupOuter());
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function children($selector = null)
    {
        $stack = [];
        foreach ($this->stack(1) as $node) {
            //          foreach($node->getElementsByTagName('*') as $newNode) {
            foreach ($node->childNodes as $newNode) {
                if ($newNode->nodeType != 1) {
                    continue;
                }
                if ($selector && !$this->is($selector, $newNode)) {
                    continue;
                }
                if ($this->elementsContainsNode($newNode, $stack)) {
                    continue;
                }
                $stack[] = $newNode;
            }
        }
        $this->elementsBackup = $this->elements;
        $this->elements       = $stack;

        return $this->newInstance();
    }

    /**
     * @param null $selector
     * @return phpQueryObject
     */
    public function ancestors($selector = null)
    {
        return $this->children($selector);
    }

    /**
     * @param $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function append($content)
    {
        return $this->insert($content, __FUNCTION__);
    }

    /**
     * @param $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function appendPHP($content)
    {
        return $this->insert("<php><!-- {$content} --></php>", 'append');
    }

    /**
     * @param $seletor
     * @return phpQueryObject
     * @throws Exception
     */
    public function appendTo($seletor)
    {
        return $this->insert($seletor, __FUNCTION__);
    }

    /**
     * @param $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function prepend($content)
    {
        return $this->insert($content, __FUNCTION__);
    }

    /**
     * @param $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function prependPHP($content)
    {
        return $this->insert("<php><!-- {$content} --></php>", 'prepend');
    }

    /**
     * @param string $seletor
     * @return phpQueryObject
     * @throws Exception
     */
    public function prependTo($seletor): self
    {
        return $this->insert($seletor, __FUNCTION__);
    }

    /**
     * @param string $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function before($content): self
    {
        return $this->insert($content, __FUNCTION__);
    }

    /**
     * @param string $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function beforePHP($content): self
    {
        return $this->insert("<php><!-- {$content} --></php>", 'before');
    }

    /**
     * @param string $seletor
     * @return phpQueryObject
     * @throws Exception
     */
    public function insertBefore($seletor): self
    {
        return $this->insert($seletor, __FUNCTION__);
    }

    /**
     * @param string $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function after($content): self
    {
        return $this->insert($content, __FUNCTION__);
    }

    /**
     * @param $content
     * @return phpQueryObject
     * @throws Exception
     */
    public function afterPHP($content): self
    {
        return $this->insert("<php><!-- {$content} --></php>", 'after');
    }

    /**
     * @param string $seletor
     * @return phpQueryObject
     * @throws Exception
     */
    public function insertAfter($seletor): self
    {
        return $this->insert($seletor, __FUNCTION__);
    }

    /**
     * Internal insert method.
     *
     * @param string $target
     * @param string $type
     * @return phpQueryObject
     * @throws Exception
     */
    protected function insert($target, $type): self
    {
        $insertFrom = [];
        $to         = false;
        switch ($type) {
            case 'appendTo':
            case 'prependTo':
            case 'insertBefore':
            case 'insertAfter':
                $to = true;
        }
        switch (\gettype($target)) {
            case 'string':
                if ($to) {
                    // INSERT TO
                    $insertFrom = $this->elements;
                    if (phpQuery::isMarkup($target)) {
                        // $target is new markup, import it
                        $insertTo = $this->documentWrapper->import($target);
                        // insert into selected element
                    } else {
                        // $tagret is a selector
                        $thisStack = $this->elements;
                        $this->toRoot();
                        $insertTo       = $this->find($target)->elements;
                        $this->elements = $thisStack;
                    }
                } else {
                    // INSERT FROM
                    $insertTo   = $this->elements;
                    $insertFrom = $this->documentWrapper->import($target);
                }
                break;
            case 'object':
                $insertFrom = $insertTo = [];
                // phpQuery
                if ($target instanceof self) {
                    if ($to) {
                        $insertTo = $target->elements;
                        if ($this->documentFragment && $this->stackIsRoot()) {
                            // get all body children
//                          $loop = $this->find('body > *')->elements;
                            // TODO test it, test it hard...
//                          $loop = $this->newInstance($this->root)->find('> *')->elements;

                            $loop = $this->root->childNodes;
                        } else {
                            $loop = $this->elements;
                        }
                        // import nodes if needed
                        $insertFrom = $this->getDocumentID() == $target->getDocumentID()
                            ? $loop
                            : $target->documentWrapper->import($loop);
                    } else {
                        $insertTo = $this->elements;
                        if ($target->documentFragment && $target->stackIsRoot()) {
                            // get all body children
                            $loop = $target->root->childNodes;
                        } else {
                            $loop = $target->elements;
                        }
                        // import nodes if needed
                        $insertFrom = $this->getDocumentID() == $target->getDocumentID()
                            ? $loop
                            : $this->documentWrapper->import($loop);
                    }
                } elseif ($target instanceof DOMNode) {
                    // import node if needed
                    if ($to) {
                        $insertTo = [$target];
                        if ($this->documentFragment && $this->stackIsRoot()) {
                            // get all body children

                            $loop = $this->root->childNodes;
                        } else {
                            $loop = $this->elements;
                        }
                        foreach ($loop as $fromNode) {// import nodes if needed
                            $insertFrom[] = !$fromNode->ownerDocument->isSameNode($target->ownerDocument)
                                ? $target->ownerDocument->importNode($fromNode, true)
                                : $fromNode;
                        }
                    } else {
                        // import node if needed
                        if (!$target->ownerDocument->isSameNode($this->document)) {
                            $target = $this->document->importNode($target, true);
                        }
                        $insertTo     = $this->elements;
                        $insertFrom[] = $target;
                    }
                }
                break;
        }
        foreach ($insertTo as $insertNumber => $toNode) {
            $nextSibling = null;
            $firstChild  = null;
            // we need static relative elements in some cases
            switch ($type) {
                case 'prependTo':
                case 'prepend':
                    $firstChild = $toNode->firstChild;
                    break;
                case 'insertAfter':
                case 'after':
                    $nextSibling = $toNode->nextSibling;
                    break;
            }
            foreach ($insertFrom as $fromNode) {
                // clone if inserted already before
                $insert = $insertNumber
                    ? $fromNode->cloneNode(true)
                    : $fromNode;
                switch ($type) {
                    case 'appendTo':
                    case 'append':
                        $toNode->appendChild($insert);
                        break;
                    case 'prependTo':
                    case 'prepend':
                        $toNode->insertBefore($insert, $firstChild);
                        break;
                    case 'insertBefore':
                    case 'before':
                        if (!$toNode->parentNode) {
                            throw new Exception("No parentNode, can't do {$type}()");
                        }
                        $toNode->parentNode->insertBefore($insert, $toNode);
                        break;
                    case 'insertAfter':
                    case 'after':
                        if (!$toNode->parentNode) {
                            throw new Exception("No parentNode, can't do {$type}()");
                        }
                        $toNode->parentNode->insertBefore($insert, $nextSibling);
                        break;
                }
                // Mutation event
                $event = new DOMEvent([
                    'target' => $insert,
                    'type'   => 'DOMNodeInserted'
                ]);
                phpQueryEvents::trigger(
                    $this->getDocumentID(),
                    $event->type,
                    [$event],
                    $insert
                );
            }
        }

        return $this;
    }

    /**
     * @param $subject
     * @return int|null|string
     * @throws Exception
     */
    public function index($subject)
    {
        $index   = -1;
        $subject = $subject instanceof self
            ? $subject->elements[0]
            : $subject;
        foreach ($this->newInstance() as $k => $node) {
            if ($node->isSameNode($subject)) {
                $index = $k;
            }
        }

        return $index;
    }

    /**
     * @param int $start
     * @param int $end
     * @return phpQueryObject
     * @throws Exception
     */
    public function slice($start, $end = null)
    {
        if ($end > 0) {
            $end -= $start;
        }

        return $this->newInstance(\array_slice($this->elements, $start, $end));
    }

    /**
     * @return phpQueryObject
     * @throws Exception
     */
    public function reverse()
    {
        $this->elementsBackup = $this->elements;
        $this->elements       = \array_reverse($this->elements);

        return $this->newInstance();
    }

    /**
     * Return joined text content.
     *
     * @param null $text
     * @param null $callback1
     * @param null $callback2
     * @param null $callback3
     * @return bool|mixed|phpQueryObject|string
     * @throws Exception
     */
    public function text($text = null, $callback1 = null, $callback2 = null, $callback3 = null)
    {
        if (isset($text)) {
            return $this->html(Text::htmlspecialchars($text));
        }
        $args   = \func_get_args();
        $args   = \array_slice($args, 1);
        $return = '';
        foreach ($this->elements as $node) {
            $text = $node->textContent;
            if ($text && \count($this->elements) > 1) {
                $text .= "\n";
            }
            foreach ($args as $callback) {
                $text = phpQuery::callbackRun($callback, [$text]);
            }
            $return .= $text;
        }

        return $return;
    }

    /**
     * @param string $class
     * @param string $file
     * @return $this
     * @throws Exception
     */
    public function plugin($class, $file = null): self
    {
        phpQuery::plugin($class, $file);

        return $this;
    }

    /**
     * Deprecated, use $pq->plugin() instead.
     *
     * @param string $class
     * @param string $file
     * @return bool
     * @throws Exception
     * @deprecated
     */
    public static function extend($class, $file = null): bool
    {
        return phpQuery::plugin($class, $file);
    }

    /**
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        $aliasMethods = ['clone', 'empty'];
        if (isset(phpQuery::$extendMethods[$method])) {
            \array_unshift($args, $this);

            return phpQuery::callbackRun(
                phpQuery::$extendMethods[$method],
                $args
            );
        }
        if (isset(phpQuery::$pluginsMethods[$method])) {
            \array_unshift($args, $this);
            $class     = phpQuery::$pluginsMethods[$method];
            $realClass = "phpQueryObjectPlugin_$class";
            $return    = \call_user_func_array(
                [$realClass, $method],
                $args
            );

            // XXX deprecate ?
            return $return ?? $this;
        }
        if (\in_array($method, $aliasMethods, true)) {
            return \call_user_func_array([$this, '_' . $method], $args);
        }
        throw new Exception("Method '{$method}' doesnt exist");
    }

    /**
     * Safe rename of next().
     *
     * Use it ONLY when need to call next() on an iterated object (in same time).
     * Normaly there is no need to do such thing ;)
     *
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function _next($selector = null)
    {
        return $this->newInstance($this->getElementSiblings('nextSibling', $selector, true));
    }

    /**
     * Use prev() and next().
     *
     * @param string|null $selector
     * @return phpQueryObject
     * @deprecated
     */
    public function _prev($selector = null)
    {
        return $this->prev($selector);
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function prev($selector = null)
    {
        return $this->newInstance($this->getElementSiblings('previousSibling', $selector, true));
    }

    /**
     * @param null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function prevAll($selector = null)
    {
        return $this->newInstance($this->getElementSiblings('previousSibling', $selector));
    }

    /**
     * @param null $selector
     * @return phpQueryObject
     * @throws Exception
     * @todo FIXME: returns source elements insted of next siblings
     */
    public function nextAll($selector = null)
    {
        return $this->newInstance($this->getElementSiblings('nextSibling', $selector));
    }

    /**
     * @param string $direction
     * @param string|null $selector
     * @param bool $limitToOne
     * @return array
     */
    protected function getElementSiblings($direction, $selector = null, $limitToOne = false): array
    {
        $stack = [];
        foreach ($this->stack() as $node) {
            $test = $node;
            while (isset($test->{$direction}) && $test->{$direction}) {
                $test = $test->{$direction};
                if (!$test instanceof DOMElement) {
                    continue;
                }
                $stack[] = $test;
                if ($limitToOne) {
                    break;
                }
            }
        }
        if ($selector) {
            $stackOld       = $this->elements;
            $this->elements = $stack;
            $stack          = $this->filter($selector, true)->stack();
            $this->elements = $stackOld;
        }

        return $stack;
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function siblings($selector = null)
    {
        $stack    = [];
        $siblings = \array_merge(
            $this->getElementSiblings('previousSibling', $selector),
            $this->getElementSiblings('nextSibling', $selector)
        );
        foreach ($siblings as $node) {
            if (!$this->elementsContainsNode($node, $stack)) {
                $stack[] = $node;
            }
        }

        return $this->newInstance($stack);
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function not($selector = null)
    {
        $stack = [];
        if ($selector instanceof self || $selector instanceof DOMNode) {
            foreach ($this->stack() as $node) {
                if ($selector instanceof self) {
                    $matchFound = false;
                    foreach ($selector->stack() as $notNode) {
                        if ($notNode->isSameNode($node)) {
                            $matchFound = true;
                        }
                    }
                    if (!$matchFound) {
                        $stack[] = $node;
                    }
                } elseif ($selector instanceof DOMNode) {
                    if (!$selector->isSameNode($node)) {
                        $stack[] = $node;
                    }
                } elseif (!$this->is($selector)) {
                    $stack[] = $node;
                }
            }
        } else {
            $orgStack = $this->stack();
            $matched  = $this->filter($selector, true)->stack();
            foreach ($orgStack as $node) {
                if (!$this->elementsContainsNode($node, $matched)) {
                    $stack[] = $node;
                }
            }
        }

        return $this->newInstance($stack);
    }

    /**
     * @param string|phpQueryObject
     * @return phpQueryObject
     * @throws Exception
     */
    public function add($selector = null)
    {
        if (!$selector) {
            return $this;
        }
        $this->elementsBackup = $this->elements;
        $found                = phpQuery::pq($selector, $this->getDocumentID());
        $this->merge($found->elements);

        return $this->newInstance();
    }

    /**
     *
     */
    protected function merge(): void
    {
        foreach (\func_get_args() as $nodes) {
            foreach ($nodes as $newNode) {
                if (!$this->elementsContainsNode($newNode)) {
                    $this->elements[] = $newNode;
                }
            }
        }
    }

    /**
     * @param DOMElement $nodeToCheck
     * @param DOMElement[]|null $elementsStack
     * @return bool
     */
    protected function elementsContainsNode($nodeToCheck, $elementsStack = null): bool
    {
        $loop = $elementsStack ?? $this->elements;
        foreach ($loop as $node) {
            if ($node->isSameNode($nodeToCheck)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function parent($selector = null)
    {
        $stack = [];
        foreach ($this->elements as $node) {
            if ($node->parentNode && !$this->elementsContainsNode($node->parentNode, $stack)) {
                $stack[] = $node->parentNode;
            }
        }
        $this->elementsBackup = $this->elements;
        $this->elements       = $stack;
        if ($selector) {
            $this->filter($selector, true);
        }

        return $this->newInstance();
    }

    /**
     * @param string|null $selector
     * @return phpQueryObject
     * @throws Exception
     */
    public function parents($selector = null)
    {
        $stack = [];
        foreach ($this->elements as $node) {
            $test = $node;
            while ($test->parentNode) {
                $test = $test->parentNode;
                if ($this->isRoot($test)) {
                    break;
                }
                if (!$this->elementsContainsNode($test, $stack)) {
                    $stack[] = $test;
                    continue;
                }
            }
        }
        $this->elementsBackup = $this->elements;
        $this->elements       = $stack;
        if ($selector) {
            $this->filter($selector, true);
        }

        return $this->newInstance();
    }

    /**
     * Internal stack iterator.
     *
     * @param array|string|null $nodeTypes
     * @return array
     */
    public function stack($nodeTypes = null): array
    {
        if (!isset($nodeTypes)) {
            return $this->elements;
        }
        if (!\is_array($nodeTypes)) {
            $nodeTypes = [$nodeTypes];
        }
        $return = [];
        foreach ($this->elements as $node) {
            if (\in_array($node->nodeType, $nodeTypes)) {
                $return[] = $node;
            }
        }

        return $return;
    }

    /**
     * @param string     $attr
     * @param            $oldAttr
     * @param            $oldValue
     * @param DOMElement $node
     */
    protected function attrEvents($attr, $oldAttr, $oldValue, $node)
    {
        // skip events for XML documents
        if (!$this->isXHTML() && !$this->isHTML()) {
            return;
        }
        $event = null;
        // identify
        $isInputValue = $node->tagName === 'input'
            && (\in_array(
                $node->getAttribute('type'),
                ['text', 'password', 'hidden'],
                true
            )
                || !$node->getAttribute('type')
            );
        $isRadio      = $node->tagName === 'input'
            && $node->getAttribute('type') === 'radio';
        $isCheckbox   = $node->tagName === 'input'
            && $node->getAttribute('type') === 'checkbox';
        $isOption     = $node->tagName === 'option';
        if ($isInputValue && $attr === 'value' && $oldValue != $node->getAttribute($attr)) {
            $event = new DOMEvent([
                'target' => $node,
                'type'   => 'change'
            ]);
        } elseif (($isRadio || $isCheckbox) && $attr === 'checked' && (
                // check
                (!$oldAttr && $node->hasAttribute($attr))
                // un-check
                || (!$node->hasAttribute($attr) && $oldAttr)
            )
        ) {
            $event = new DOMEvent([
                'target' => $node,
                'type'   => 'change'
            ]);
        } elseif ($isOption && $node->parentNode && $attr === 'selected' && (
                // select
                (!$oldAttr && $node->hasAttribute($attr))
                // un-select
                || (!$node->hasAttribute($attr) && $oldAttr)
            )
        ) {
            $event = new DOMEvent([
                'target' => $node->parentNode,
                'type'   => 'change'
            ]);
        }
        if ($event) {
            phpQueryEvents::trigger(
                $this->getDocumentID(),
                $event->type,
                [$event],
                $node
            );
        }
    }

    /**
     * @param null $attr
     * @param null $value
     * @return array|null|phpQueryObject|string
     */
    public function attr($attr = null, $value = null)
    {
        foreach ($this->stack(1) as $node) {
            if ($value !== null) {
                $loop = $attr === '*'
                    ? $this->getNodeAttrs($node)
                    : [$attr];
                foreach ($loop as $a) {
                    $oldValue = $node->getAttribute($a);
                    $oldAttr  = $node->hasAttribute($a);
                    // TODO raises an error when charset other than UTF-8
                    // while document's charset is also not UTF-8
                    @$node->setAttribute($a, $value);
                    $this->attrEvents($a, $oldAttr, $oldValue, $node);
                }
            } else {
                if ($attr === '*') {
                    // jQuery difference
                    $return = [];
                    foreach ($node->attributes as $n => $v) {
                        $return[$n] = $v->value;
                    }

                    return $return;
                }

                return $node->hasAttribute($attr)
                    ? $node->getAttribute($attr)
                    : null;
            }
        }

        return $value === null ? '' : $this;
    }

    /**
     * @param DOMElement $node
     * @return array
     */
    protected function getNodeAttrs($node): array
    {
        $return = [];
        foreach ($node->attributes as $n => $o) {
            $return[] = $n;
        }

        return $return;
    }

    /**
     * @param string $attr
     * @param        $code
     * @return $this|array
     */
    public function attrPHP($attr, $code)
    {
        if ($code !== null) {
            $value = '<' . '?php ' . $code . ' ?' . '>';
            // TODO tempolary solution
            // http://code.google.com/p/phpquery/issues/detail?id=17
//          if (function_exists('mb_detect_encoding') && mb_detect_encoding($value) == 'ASCII')
//              $value  = mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES');
        }
        foreach ($this->stack(1) as $node) {
            if ($code !== null) {
                $node->setAttribute($attr, $value);
            } else {
                if ($attr === '*') {
                    // jQuery diff
                    $return = [];
                    foreach ($node->attributes as $n => $v) {
                        $return[$n] = $v->value;
                    }

                    return $return;
                }

                return $node->getAttribute($attr);
            }
        }

        return $this;
    }

    /**
     * @param string $attr
     * @return $this
     */
    public function removeAttr($attr): self
    {
        foreach ($this->stack(1) as $node) {
            $loop = $attr === '*'
                ? $this->getNodeAttrs($node)
                : [$attr];
            foreach ($loop as $a) {
                $oldValue = $node->getAttribute($a);
                $node->removeAttribute($a);
                $this->attrEvents($a, $oldValue, null, $node);
            }
        }

        return $this;
    }

    /**
     * Return form element value.
     *
     * @param array|string $val
     * @return string
     */
    public function val($val = null): string
    {
        if (!isset($val)) {
            if ($this->eq(0)->is('select')) {
                $selected = $this->eq(0)->find('option[selected=selected]');
                if ($selected->is('[value]')) {
                    return $selected->attr('value');
                }

                return $selected->text();
            }
            if ($this->eq(0)->is('textarea')) {
                return $this->eq(0)->markup();
            }

            return $this->eq(0)->attr('value');
        }
        $_val = null;
        foreach ($this->stack(1) as $node) {
            $node = \pq($node, $this->getDocumentID());
            if (\is_array($val) && \in_array($node->attr('type'), ['checkbox', 'radio'], true)) {
                $isChecked = \in_array($node->attr('value'), $val)
                    || \in_array($node->attr('name'), $val);
                if ($isChecked) {
                    $node->attr('checked', 'checked');
                } else {
                    $node->removeAttr('checked');
                }
            } elseif ($node->get(0)->tagName === 'select') {
                if (!isset($_val)) {
                    $_val = [];
                    if (!\is_array($val)) {
                        $_val = [(string)$val];
                    } else {
                        foreach ($val as $v) {
                            $_val[] = $v;
                        }
                    }
                }
                foreach ($node['option']->stack(1) as $option) {
                    $option = \pq($option, $this->getDocumentID());
                    // XXX: workaround for string comparsion, see issue #96
                    // http://code.google.com/p/phpquery/issues/detail?id=96
                    $selected = $option->attr('value') === null
                        ? \in_array($option->markup(), $_val)
                        : \in_array($option->attr('value'), $_val);
                    if ($selected) {
                        $option->attr('selected', 'selected');
                    } else {
                        $option->removeAttr('selected');
                    }
                }
            } elseif ($node->get(0)->tagName === 'textarea') {
                $node->markup($val);
            } else {
                $node->attr('value', $val);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function andSelf(): self
    {
        if ($this->previous) {
            $this->elements = \array_merge($this->elements, $this->previous->elements);
        }

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClass($className): self
    {
        if (!$className) {
            return $this;
        }
        foreach ($this->stack(1) as $node) {
            if (!$this->is(".$className", $node)) {
                $node->setAttribute('class', \trim($node->getAttribute('class') . ' ' . $className));
            }
        }

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClassPHP($className): self
    {
        foreach ($this->stack(1) as $node) {
            $classes  = $node->getAttribute('class');
            $newValue = $classes
                ? $classes . ' <' . '?php ' . $className . ' ?' . '>'
                : '<' . '?php ' . $className . ' ?' . '>';
            $node->setAttribute('class', $newValue);
        }

        return $this;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function hasClass($className): bool
    {
        foreach ($this->stack(1) as $node) {
            if ($this->is('.' . $className, $node)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function removeClass($className): self
    {
        foreach ($this->stack(1) as $node) {
            $classes = \explode(' ', $node->getAttribute('class'));
            if (\in_array($className, $classes, true)) {
                $classes = \array_diff($classes, [$className]);
                if ($classes) {
                    $node->setAttribute('class', \implode(' ', $classes));
                } else {
                    $node->removeAttribute('class');
                }
            }
        }

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function toggleClass($className): self
    {
        foreach ($this->stack(1) as $node) {
            if ($this->is($node, '.' . $className)) {
                $this->removeClass($className);
            } else {
                $this->addClass($className);
            }
        }

        return $this;
    }

    /**
     * Proper name without underscore (just ->empty()) also works.
     *
     * Removes all child nodes from the set of matched elements.
     *
     * Example:
     * pq("p")._empty()
     *
     * HTML:
     * <p>Hello, <span>Person</span> <a href="#">and person</a></p>
     *
     * Result:
     * [ <p></p> ]
     *
     * @return phpQueryObject
     */
    public function _empty(): self
    {
        foreach ($this->stack(1) as $node) {
            // thx to 'dave at dgx dot cz'
            $node->nodeValue = '';
        }

        return $this;
    }

    /**
     * @param      $callback
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @return $this
     */
    public function each($callback, $param1 = null, $param2 = null, $param3 = null): self
    {
        $paramStructure = null;
        if (\func_num_args() > 1) {
            $paramStructure = \func_get_args();
            $paramStructure = \array_slice($paramStructure, 1);
        }
        foreach ($this->elements as $v) {
            phpQuery::callbackRun($callback, [$v], $paramStructure);
        }

        return $this;
    }

    /**
     * @param      $callback
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @return $this
     */
    public function callback($callback, $param1 = null, $param2 = null, $param3 = null): self
    {
        $params    = \func_get_args();
        $params[0] = $this;
        phpQuery::callbackRun($callback, $params);

        return $this;
    }

    /**
     * @param      $callback
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @return phpQueryObject
     * @throws Exception
     */
    public function map($callback, $param1 = null, $param2 = null, $param3 = null)
    {
        $params = \func_get_args();
        \array_unshift($params, $this->elements);

        return $this->newInstance(\call_user_func_array([phpQuery::class, 'map'], $params));
    }

    /**
     * @param      $key
     * @param null $value
     * @return $this|mixed
     */
    public function data($key, $value = null)
    {
        if (!isset($value)) {
            // TODO? implement specific jQuery behavior od returning parent values
            // is child which we look up doesn't exist
            return phpQuery::data($this->get(0), $key, $value, $this->getDocumentID());
        }
        foreach ($this as $node) {
            phpQuery::data($node, $key, $value, $this->getDocumentID());
        }

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeData($key): self
    {
        foreach ($this as $node) {
            phpQuery::removeData($node, $key, $this->getDocumentID());
        }

        return $this;
    }

    /**
     *
     */
    public function rewind(): void
    {
        $this->elementsBackup    = $this->elements;
        $this->elementsInterator = $this->elements;
        $this->valid             = isset($this->elements[0])
            ? 1 : 0;
        $this->current           = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->elementsInterator[$this->current];
    }

    /**
     * @return null
     */
    public function key()
    {
        return $this->current;
    }

    /**
     * Double-function method.
     *
     * First: main iterator interface method.
     * Second: Returning next sibling, alias for _next().
     *
     * Proper functionality is choosed automagicaly.
     *
     * @param string $cssSelector
     * @return phpQueryObject|void
     * @see phpQueryObject::_next()
     */
    public function next($cssSelector = null)
    {
        $this->valid = isset($this->elementsInterator[$this->current + 1]);
        if (!$this->valid && $this->elementsInterator) {
            $this->elementsInterator = null;
        } elseif ($this->valid) {
            $this->current++;
        } else {
            return $this->_next($cssSelector);
        }
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->valid;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->find($offset)->size() > 0;
    }

    /**
     * @param mixed $offset
     * @return phpQueryObject
     */
    public function offsetGet($offset)
    {
        return $this->find($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        $this->find($offset)->html($value);
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Can't do unset, use array interface only for calling queries and replacing HTML.");
    }

    /**
     * @param null $oneNode
     * @param null $namespace
     * @return array|mixed
     */
    protected function getNodeXpath($oneNode = null, $namespace = null)
    {
        $return = [];
        $loop   = $oneNode
            ? [$oneNode]
            : $this->elements;
        foreach ($loop as $node) {
            if ($node instanceof DOMDocument) {
                $return[] = '';
                continue;
            }
            $xpath = [];
            while (!($node instanceof DOMDocument)) {
                $i       = 1;
                $sibling = $node;
                while ($sibling->previousSibling) {
                    $sibling   = $sibling->previousSibling;
                    $isElement = $sibling instanceof DOMElement;
                    if ($isElement && $sibling->tagName === $node->tagName) {
                        $i++;
                    }
                }
                $xpath[] = $this->isXML()
                    ? "*[local-name()='{$node->tagName}'][{$i}]"
                    : "{$node->tagName}[{$i}]";
                $node    = $node->parentNode;
            }
            $xpath    = \implode('/', \array_reverse($xpath));
            $return[] = '/' . $xpath;
        }

        return $oneNode
            ? $return[0]
            : $return;
    }

    /**
     * @param DOMElement|null $oneNode
     * @return array|mixed
     */
    public function whois($oneNode = null)
    {
        $return = [];
        $loop   = $oneNode
            ? [$oneNode]
            : $this->elements;
        foreach ($loop as $node) {
            if (isset($node->tagName)) {
                $tag      = \in_array($node->tagName, ['php', 'js'], true)
                    ? \strtoupper($node->tagName)
                    : $node->tagName;
                $return[] = $tag
                    . ($node->getAttribute('id')
                        ? '#' . $node->getAttribute('id') : '')
                    . ($node->getAttribute('class')
                        ? '.' . \implode('.', \explode(' ', $node->getAttribute('class'))) : '')
                    . ($node->getAttribute('name')
                        ? '[name="' . $node->getAttribute('name') . '"]' : '')
                    . ($node->getAttribute('value') && \strpos($node->getAttribute('value'), '<' . '?php') === false
                        ? '[value="' . \substr(\str_replace("\n", '', $node->getAttribute('value')), 0, 15) . '"]' : '')
                    . ($node->getAttribute('value') && \strpos($node->getAttribute('value'), '<' . '?php') !== false
                        ? '[value=PHP]' : '')
                    . ($node->getAttribute('selected')
                        ? '[selected]' : '')
                    . ($node->getAttribute('checked')
                        ? '[checked]' : '');
            } elseif ($node instanceof DOMText && \trim($node->textContent)) {
                $return[] = 'Text:' . \substr(\str_replace("\n", ' ', $node->textContent), 0, 15);
            }
        }

        return $oneNode && isset($return[0])
            ? $return[0]
            : $return;
    }

    /**
     * Dump htmlOuter and preserve chain. Usefull for debugging.
     *
     * @return $this
     */
    public function dump(): self
    {
        print 'DUMP #' . (phpQuery::$dumpCount++) . ' ';
        phpQuery::$debug = false;
        \var_dump($this->htmlOuter());

        return $this;
    }

    /**
     * @return $this
     */
    public function dumpWhois(): self
    {
        print 'DUMP #' . (phpQuery::$dumpCount++) . ' ';
        $debug           = phpQuery::$debug;
        phpQuery::$debug = false;
        \var_dump('whois', $this->whois());
        phpQuery::$debug = $debug;

        return $this;
    }

    /**
     * @return $this
     */
    public function dumpLength(): self
    {
        print 'DUMP #' . (phpQuery::$dumpCount++) . ' ';
        $debug           = phpQuery::$debug;
        phpQuery::$debug = false;
        \var_dump('length', $this->length);
        phpQuery::$debug = $debug;

        return $this;
    }

    /**
     * @param bool $html
     * @param bool $title
     * @return $this
     */
    public function dumpTree($html = true, $title = true): self
    {
        $output          = $title
            ? 'DUMP #' . (phpQuery::$dumpCount++) . " \n" : '';
        $debug           = phpQuery::$debug;
        phpQuery::$debug = false;
        foreach ($this->stack() as $node) {
            $output .= $this->__dumpTree($node);
        }
        phpQuery::$debug = $debug;
        echo $html
            ? \nl2br(\str_replace(' ', '&nbsp;', $output))
            : $output;

        return $this;
    }

    /**
     * @param DOMNode $node
     * @param int     $intend
     * @return string
     */
    private function __dumpTree($node, $intend = 0): string
    {
        $whois  = $this->whois($node);
        $return = '';
        if ($whois) {
            $return .= \str_repeat(' - ', $intend) . $whois . "\n";
        }
        if (isset($node->childNodes)) {
            foreach ($node->childNodes as $chNode) {
                $return .= $this->__dumpTree($chNode, $intend + 1);
            }
        }

        return $return;
    }

    /**
     * Dump htmlOuter and stop script execution. Useful for debugging.
     */
    public function dumpDie(): void
    {
        print __FILE__ . ':' . __LINE__;
        \var_dump($this->htmlOuter());
        die();
    }
}
