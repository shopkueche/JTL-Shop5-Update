<?php
/**
 * phpQuery is a server-side, chainable, CSS3 selector driven
 * Document Object Model (DOM) API based on jQuery JavaScript Library.
 *
 * @version 0.9.5
 * @link http://code.google.com/p/phpquery/
 * @link http://phpquery-library.blogspot.com/
 * @link http://jquery.com/
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package phpQuery
 */

namespace JTL\phpQuery;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use Iterator;

/**
 * Class phpQuery
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package JTL\phpQuery
 */
abstract class phpQuery
{
    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @var array
     */
    public static $documents = [];

    /**
     * @var null
     */
    public static $defaultDocumentID;

    /**
     * Applies only to HTML.
     *
     * @var string
     */
    public static $defaultDoctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

    /**
     * @var string
     */
    public static $defaultCharset = 'UTF-8';

    /**
     * @var array
     */
    public static $plugins = [];

    /**
     * @var array
     */
    public static $pluginsLoaded = [];

    /**
     * @var array
     */
    public static $pluginsMethods = [];

    /**
     * @var array
     */
    public static $pluginsStaticMethods = [];

    /**
     * @var array
     */
    public static $extendMethods = [];

    /**
     * @var array
     */
    public static $extendStaticMethods = [];

    /**
     * @var string|null
     */
    public static $lastModified;

    /**
     * @var int
     */
    public static $active = 0;

    /**
     * @var int
     */
    public static $dumpCount = 0;

    /**
     * Multi-purpose function.
     * Use pq() as shortcut.
     *
     * In below examples, $pq is any result of pq(); function.
     *
     * 1. Import markup into existing document (without any attaching):
     * - Import into selected document:
     *   pq('<div/>')                // DOESNT accept text nodes at beginning of input string !
     * - Import into document with ID from $pq->getDocumentID():
     *   pq('<div/>', $pq->getDocumentID())
     * - Import into same document as DOMNode belongs to:
     *   pq('<div/>', DOMNode)
     * - Import into document from phpQuery object:
     *   pq('<div/>', $pq)
     *
     * 2. Run query:
     * - Run query on last selected document:
     *   pq('div.myClass')
     * - Run query on document with ID from $pq->getDocumentID():
     *   pq('div.myClass', $pq->getDocumentID())
     * - Run query on same document as DOMNode belongs to and use node(s)as root for query:
     *   pq('div.myClass', DOMNode)
     * - Run query on document from phpQuery object
     *   and use object's stack as root node(s) for query:
     *   pq('div.myClass', $pq)
     *
     * @param string|DOMNode|DOMNodeList|array $arg1 HTML markup, CSS Selector, DOMNode or array of DOMNodes
     * @param string|phpQueryObject|DOMNode    $context DOM ID from $pq->getDocumentID(), phpQuery object (determines
     *     also query root) or DOMNode (determines also query root)
     * @return phpQueryObject|false
     * phpQuery object or false in case of error.
     * @throws Exception
     */
    public static function pq($arg1, $context = null)
    {
        if ($context === null && $arg1 instanceof DOMNode) {
            foreach (self::$documents as $documentWrapper) {
                $compare = $arg1 instanceof DOMDocument
                    ? $arg1 : $arg1->ownerDocument;
                if ($documentWrapper->document->isSameNode($compare)) {
                    $context = $documentWrapper->id;
                }
            }
        }
        if (!$context) {
            $domId = self::$defaultDocumentID;
            if (!$domId) {
                throw new Exception("Can't use last created DOM, because there isn't any.
                 Use phpQuery::newDocument() first.");
            }
        } elseif (\is_object($context) && $context instanceof phpQueryObject) {
            $domId = $context->getDocumentID();
        } elseif ($context instanceof DOMDocument) {
            $domId = self::getDocumentID($context);
            if (!$domId) {
                $domId = self::newDocument($context)->getDocumentID();
            }
        } elseif ($context instanceof DOMNode) {
            $domId = self::getDocumentID($context);
            if (!$domId) {
                throw new Exception('Orphaned DOMNode');
            }
        } else {
            $domId = $context;
        }
        if ($arg1 instanceof phpQueryObject) {
            /**
             * Return $arg1 or import $arg1 stack if document differs:
             * pq(pq('<div/>'))
             */
            if ($arg1->getDocumentID() === $domId) {
                return $arg1;
            }
            $class = \get_class($arg1);
            // support inheritance by passing old object to overloaded constructor
            $phpQuery           = $class !== __CLASS__
                ? new $class($arg1, $domId)
                : new phpQueryObject($domId);
            $phpQuery->elements = [];
            foreach ($arg1->elements as $node) {
                $phpQuery->elements[] = $phpQuery->document->importNode($node, true);
            }

            return $phpQuery;
        }
        if ($arg1 instanceof DOMNode || (\is_array($arg1) && isset($arg1[0]) && $arg1[0] instanceof DOMNode)) {
            $phpQuery = new phpQueryObject($domId);
            if (!($arg1 instanceof DOMNodeList) && !\is_array($arg1)) {
                $arg1 = [$arg1];
            }
            $phpQuery->elements = [];
            foreach ($arg1 as $node) {
                $sameDocument         = $node->ownerDocument instanceof DOMDocument
                    && !$node->ownerDocument->isSameNode($phpQuery->document);
                $phpQuery->elements[] = $sameDocument
                    ? $phpQuery->document->importNode($node, true)
                    : $node;
            }

            return $phpQuery;
        }
        if (self::isMarkup($arg1)) {
            /**
             * Import HTML:
             * pq('<div/>')
             */
            $phpQuery = new phpQueryObject($domId);

            return $phpQuery->newInstance($phpQuery->documentWrapper->import($arg1));
        }
        /**
         * Run CSS query:
         * pq('div.myClass')
         */
        $phpQuery = new phpQueryObject($domId);
        if ($context && $context instanceof phpQueryObject) {
            $phpQuery->elements = $context->elements;
        } elseif ($context && $context instanceof DOMNodeList) {
            $phpQuery->elements = [];
            foreach ($context as $node) {
                $phpQuery->elements[] = $node;
            }
        } elseif ($context && $context instanceof DOMNode) {
            $phpQuery->elements = [$context];
        }

        return $phpQuery->find($arg1);
    }

    /**
     * Sets default document to $id. Document has to be loaded prior
     * to using this method.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     *
     * @param string $id
     */
    public static function selectDocument($id): void
    {
        $id                      = self::getDocumentID($id);
        self::$defaultDocumentID = self::getDocumentID($id);
    }

    /**
     * Returns document with id $id or last used as phpQueryObject.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     *
     * @param string $id
     * @return phpQueryObject
     * @throws Exception
     * @see phpQuery::selectDocument()
     */
    public static function getDocument($id = null): phpQueryObject
    {
        if ($id) {
            self::selectDocument($id);
        } else {
            $id = self::$defaultDocumentID;
        }

        return new phpQueryObject($id);
    }

    /**
     * Creates new document from markup.
     *
     * @param string|null $markup
     * @param string|null $contentType
     * @return phpQueryObject
     * @throws Exception
     */
    public static function newDocument($markup = null, $contentType = null): phpQueryObject
    {
        if (!$markup) {
            $markup = '';
        }
        $documentID = self::createDocumentWrapper($markup, $contentType);

        return new phpQueryObject($documentID);
    }

    /**
     * Creates new document from markup.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentHTML($markup = null, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentXML($markup = null, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "text/xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentXHTML($markup = null, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string|null $markup
     * @param string      $contentType
     * @return phpQueryObject
     */
    public static function newDocumentPHP($markup = null, $contentType = 'text/html'): phpQueryObject
    {
        // TODO pass charset to phpToMarkup if possible (use DOMDocumentWrapper function)
        return self::newDocument(self::phpToMarkup($markup, self::$defaultCharset), $contentType);
    }

    /**
     * @param string $php
     * @param string $charset
     * @return string
     */
    public static function phpToMarkup($php, $charset = 'utf-8'): string
    {
        $regexes = [
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)<' . '?php?(.*?)(?:\\?>)([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)<' . '?php?(.*?)(?:\\?>)([^"]*)"@s',
        ];
        foreach ($regexes as $regex) {
            while (\preg_match($regex, $php, $matches)) {
                $php = \preg_replace_callback(
                    $regex,
                    ['phpQuery', '_phpToMarkupCallback'],
                    $php
                );
            }
        }

        return \preg_replace('@(^|>[^<]*)+?(<\?php(.*?)(\?>))@s', '\\1<php><!-- \\3 --></php>', $php);
    }

    /**
     * @param string $m
     * @param string $charset
     * @return string
     */
    public static function _phpToMarkupCallback($m, $charset = 'utf-8'): string
    {
        return $m[1] . $m[2]
            . \htmlspecialchars('<?php' . $m[4] . '?>', ENT_QUOTES | ENT_NOQUOTES, $charset)
            . $m[5] . $m[2];
    }

    /**
     * @param string $m
     * @return string
     */
    public static function _markupToPHPCallback($m): string
    {
        return '<?php ' . \htmlspecialchars_decode($m[1]) . ' ?>';
    }

    /**
     * Converts document markup containing PHP code generated by phpQuery::php()
     * into valid (executable) PHP code syntax.
     *
     * @param string|phpQueryObject $content
     * @return string PHP code.
     */
    public static function markupToPHP($content): string
    {
        if ($content instanceof phpQueryObject) {
            $content = $content->markupOuter();
        }
        /* <php>...</php> to <?php...? > */
        $content = \preg_replace_callback(
            '@<php>\s*<!--(.*?)-->\s*</php>@s',
            ['phpQuery', '_markupToPHPCallback'],
            $content
        );
        /* <node attr='< ?php ? >'> extra space added to save highlighters */
        $regexes = [
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^"]*)"@s',
        ];
        foreach ($regexes as $regex) {
            while (\preg_match($regex, $content)) {
                $content = \preg_replace_callback(
                    $regex,
                    static function ($m) {
                        return $m[1] . $m[2] . $m[3] . '<?php '
                            . \str_replace(
                                ['%20', '%3E', '%09', '&#10;', '&#9;', '%7B', '%24', '%7D', '%22', '%5B', '%5D'],
                                [' ', '>', '	', "\n", '	', '{', '$', '}', '"', '[', ']'],
                                \htmlspecialchars_decode($m[4])
                            ) . ' ?>' . $m[5] . $m[2];
                    },
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Creates new document from file $file.
     *
     * @param string      $file URLs allowed. See File wrapper page at php.net for more supported sources.
     * @param string|null $contentType
     * @return phpQueryObject
     * @throws Exception
     */
    public static function newDocumentFile($file, $contentType = null): phpQueryObject
    {
        return new phpQueryObject(self::createDocumentWrapper(\file_get_contents($file), $contentType));
    }

    /**
     * Creates new document from markup.
     *
     * @param string      $file
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentFileHTML($file, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string      $file
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentFileXML($file, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "text/xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string      $file
     * @param string|null $charset
     * @return phpQueryObject
     */
    public static function newDocumentFileXHTML($file, $charset = null): phpQueryObject
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string      $file
     * @param string|null $contentType
     * @return phpQueryObject
     */
    public static function newDocumentFilePHP($file, $contentType = null)
    {
        return self::newDocumentPHP(\file_get_contents($file), $contentType);
    }

    /**
     * @param string      $html
     * @param string|null $contentType
     * @param string|null $documentID
     * @return null|string
     * @throws Exception
     *
     * @todo support PHP tags in input
     * @todo support passing DOMDocument object from self::loadDocument
     */
    protected static function createDocumentWrapper(
        string $html,
        string $contentType = null,
        $documentID = null
    ): ?string {
        $document = null;
        if (($html instanceof DOMDocument) && self::getDocumentID($html)) {
            // document already exists in phpQuery::$documents, make a copy
            $wrapper = clone $html;
        } else {
            $wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
        }
        // bind document
        self::$documents[$wrapper->id] = $wrapper;
        // remember last loaded document
        self::selectDocument($wrapper->id);

        return $wrapper->id;
    }

    /**
     * Extend class namespace.
     *
     * @param string|array $target
     * @param string|array $source
     * @return bool
     * @throws Exception
     * @TODO support string $source
     */
    public static function extend($target, $source): bool
    {
        switch ($target) {
            case 'phpQueryObject':
                $targetRef  = &self::$extendMethods;
                $targetRef2 = &self::$pluginsMethods;
                break;
            case 'phpQuery':
                $targetRef  = &self::$extendStaticMethods;
                $targetRef2 = &self::$pluginsStaticMethods;
                break;
            default:
                throw new Exception('Unsupported $target type');
        }
        if (\is_string($source)) {
            $source = [$source => $source];
        }
        foreach ($source as $method => $callback) {
            if (isset($targetRef[$method])) {
                continue;
            }
            if (isset($targetRef2[$method])) {
                continue;
            }
            $targetRef[$method] = $callback;
        }

        return true;
    }

    /**
     * Extend phpQuery with $class from $file.
     *
     * @param string $class - Extending class name. Real class name can be prepended phpQuery_.
     * @param string $file - Filename to include. Defaults to "{$class}.php".
     * @return bool
     * @throws Exception
     */
    public static function plugin($class, $file = null): bool
    {
        if (\in_array($class, self::$pluginsLoaded, true)) {
            return true;
        }
        if (!$file) {
            $file = $class . '.php';
        }
        $objectClassExists = \class_exists('phpQueryObjectPlugin_' . $class);
        $staticClassExists = \class_exists('phpQueryPlugin_' . $class);
        if (!$objectClassExists && !$staticClassExists) {
            require_once $file;
        }
        self::$pluginsLoaded[] = $class;
        // static methods
        if (\class_exists('phpQueryPlugin_' . $class)) {
            $realClass = 'phpQueryPlugin_' . $class;
            $vars      = \get_class_vars($realClass);
            $loop      = isset($vars['phpQueryMethods'])
            && $vars['phpQueryMethods'] !== null
                ? $vars['phpQueryMethods']
                : \get_class_methods($realClass);
            foreach ($loop as $method) {
                if ($method === '__initialize') {
                    continue;
                }
                if (!\is_callable([$realClass, $method])) {
                    continue;
                }
                if (isset(self::$pluginsStaticMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$class}' conflicts with same method from plugin '" .
                        self::$pluginsStaticMethods[$method] . "'");
                }
                self::$pluginsStaticMethods[$method] = $class;
            }
            if (\method_exists($realClass, '__initialize')) {
                \call_user_func_array([$realClass, '__initialize'], []);
            }
        }
        // object methods
        if (\class_exists('phpQueryObjectPlugin_' . $class)) {
            $realClass = 'phpQueryObjectPlugin_' . $class;
            $vars      = \get_class_vars($realClass);
            $loop      = isset($vars['phpQueryMethods'])
            && $vars['phpQueryMethods'] !== null
                ? $vars['phpQueryMethods']
                : \get_class_methods($realClass);
            foreach ($loop as $method) {
                if (!\is_callable([$realClass, $method])) {
                    continue;
                }
                if (isset(self::$pluginsMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$class}' conflicts with same method from plugin '" .
                        self::$pluginsMethods[$method] . "'");
                    continue;
                }
                self::$pluginsMethods[$method] = $class;
            }
        }

        return true;
    }

    /**
     * Unloades all or specified document from memory.
     *
     * @param mixed $id @see phpQuery::getDocumentID() for supported types.
     */
    public static function unloadDocuments($id = null): void
    {
        if (isset($id)) {
            if ($id = self::getDocumentID($id)) {
                unset(self::$documents[$id]);
            }
        } else {
            foreach (self::$documents as $k => $v) {
                unset(self::$documents[$k]);
            }
        }
    }

    /**
     * Parses phpQuery object or HTML result against PHP tags and makes them active.
     *
     * @param phpQuery|string $content
     * @return string
     * @deprecated
     */
    public static function unsafePHPTags($content): string
    {
        return self::markupToPHP($content);
    }

    /**
     * @param DOMNodeList $list
     * @return array
     */
    public static function DOMNodeListToArray($list): array
    {
        $array = [];
        if (!$list) {
            return $array;
        }
        foreach ($list as $node) {
            $array[] = $node;
        }

        return $array;
    }

    /**
     * Checks if $input is HTML string, which has to start with '<'.
     *
     * @param array|string $input
     * @return bool
     */
    public static function isMarkup($input): bool
    {
        return !\is_array($input) && \strpos(\trim($input), '<') === 0;
    }

    /**
     * @param mixed $text
     */
    public static function debug($text): void
    {
        if (self::$debug) {
            \var_dump($text);
        }
    }

    /**
     * @param array|phpQuery $data
     * @return string
     */
    public static function param($data): string
    {
        return \http_build_query($data, null, '&');
    }

    /**
     * Returns JSON representation of $data.
     *
     * @param mixed $data
     * @return string|bool
     */
    public static function toJSON($data)
    {
        return \json_encode($data);
    }

    /**
     * Parses JSON into proper PHP type.
     *
     * @param string $json
     * @return mixed
     */
    public static function parseJSON($json)
    {
        return \json_decode(\trim($json), true);
    }

    /**
     * Returns source's document ID.
     *
     * @param DOMNode|phpQueryObject|string $source
     * @return string
     */
    public static function getDocumentID($source): string
    {
        if ($source instanceof DOMDocument) {
            foreach (self::$documents as $id => $document) {
                if ($source->isSameNode($document->document)) {
                    return $id;
                }
            }
        } elseif ($source instanceof DOMNode) {
            foreach (self::$documents as $id => $document) {
                if ($source->ownerDocument->isSameNode($document->document)) {
                    return $id;
                }
            }
        } else {
            if ($source instanceof phpQueryObject) {
                return $source->getDocumentID();
            }
            if (\is_string($source) && isset(self::$documents[$source])) {
                return $source;
            }
        }

        return '';
    }

    /**
     * Get DOMDocument object related to $source.
     * Returns null if such document doesn't exist.
     *
     * @param DOMNode|phpQueryObject|string $source
     * @return string|DOMDocument|null
     */
    public static function getDOMDocument($source)
    {
        if ($source instanceof DOMDocument) {
            return $source;
        }
        $id = self::getDocumentID($source);

        return $id
            ? self::$documents[$id]['document']
            : null;
    }

    /**
     * @param object $object
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.makeArray
     */
    public static function makeArray($object): array
    {
        $array = [];
        if (\is_object($object) && $object instanceof DOMNodeList) {
            foreach ($object as $value) {
                $array[] = $value;
            }
        } elseif (\is_object($object) && !($object instanceof Iterator)) {
            foreach (\get_object_vars($object) as $name => $value) {
                $array[0][$name] = $value;
            }
        } else {
            foreach ($object as $name => $value) {
                $array[0][$name] = $value;
            }
        }

        return $array;
    }

    /**
     * @param object   $object
     * @param callable $callback
     * @param null     $param1
     * @param null     $param2
     * @param null     $param3
     * @link http://docs.jquery.com/Utilities/jQuery.each
     */
    public static function each($object, $callback, $param1 = null, $param2 = null, $param3 = null): void
    {
        $paramStructure = null;
        if (\func_num_args() > 2) {
            $paramStructure = \func_get_args();
            $paramStructure = \array_slice($paramStructure, 2);
        }
        if (\is_object($object) && !($object instanceof Iterator)) {
            foreach (\get_object_vars($object) as $name => $value) {
                self::callbackRun($callback, [$name, $value], $paramStructure);
            }
        } else {
            foreach ($object as $name => $value) {
                self::callbackRun($callback, [$name, $value], $paramStructure);
            }
        }
    }

    /**
     * @param array    $array
     * @param callable $callback
     * @param null     $param1
     * @param null     $param2
     * @param null     $param3
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.map
     */
    public static function map($array, $callback, $param1 = null, $param2 = null, $param3 = null): array
    {
        $result         = [];
        $paramStructure = null;
        if (\func_num_args() > 2) {
            $paramStructure = \func_get_args();
            $paramStructure = \array_slice($paramStructure, 2);
        }
        foreach ($array as $v) {
            $vv = self::callbackRun($callback, [$v], $paramStructure);
            if (\is_array($vv)) {
                foreach ($vv as $vvv) {
                    $result[] = $vvv;
                }
            } elseif ($vv !== null) {
                $result[] = $vv;
            }
        }

        return $result;
    }

    /**
     * @param callable|null $callback
     * @param array                                      $params
     * @param array|null                                 $paramStructure
     * @return bool|mixed|void
     */
    public static function callbackRun($callback, $params = [], $paramStructure = null)
    {
        if (!$callback) {
            return;
        }
        if (!$paramStructure) {
            return \call_user_func_array($callback, $params);
        }
        $p = 0;
        foreach ($paramStructure as $i => $v) {
            $paramStructure[$i] = $v instanceof CallbackParam
                ? $params[$p++]
                : $v;
        }

        return \call_user_func_array($callback, $paramStructure);
    }

    /**
     * Merge 2 phpQuery objects.
     *
     * @param phpQueryObject $one
     * @param phpQueryObject $two
     * @return array
     */
    public static function merge($one, $two): array
    {
        $elements = $one->elements;
        foreach ($two->elements as $node) {
            $exists = false;
            foreach ($elements as $node2) {
                if ($node2->isSameNode($node)) {
                    $exists = true;
                }
            }
            if (!$exists) {
                $elements[] = $node;
            }
        }

        return $elements;
    }

    /**
     * @param array    $array
     * @param callable $callback
     * @param bool     $invert
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.grep
     */
    public static function grep($array, $callback, bool $invert = false): array
    {
        $result = [];
        foreach ($array as $k => $v) {
            $r = $callback($v, $k);
            if ($r === !$invert) {
                $result[] = $v;
            }
        }

        return $result;
    }

    /**
     * @param string $code
     * @return string
     */
    public static function php($code): string
    {
        return self::code('php', $code);
    }

    /**
     * @param string $type
     * @param string $code
     * @return string
     */
    public static function code($type, $code): string
    {
        return "<$type><!-- " . \trim($code) . " --></$type>";
    }

    /**
     * @param string $method
     * @param array  $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return \call_user_func_array([self::$plugins, $method], $params);
    }

    /**
     * @param DOMElement|mixed $node
     * @param string           $documentID
     * @return mixed
     */
    protected static function dataSetupNode($node, $documentID)
    {
        foreach (self::$documents[$documentID]->dataNodes as $dataNode) {
            if ($node->isSameNode($dataNode)) {
                return $dataNode;
            }
        }
        self::$documents[$documentID]->dataNodes[] = $node;

        return $node;
    }

    /**
     * @param DOMElement $node
     * @param string     $documentID
     */
    protected static function dataRemoveNode($node, $documentID): void
    {
        foreach (self::$documents[$documentID]->dataNodes as $k => $dataNode) {
            if ($node->isSameNode($dataNode)) {
                unset(
                    self::$documents[$documentID]->dataNodes[$k],
                    self::$documents[$documentID]->data[$dataNode->dataID]
                );
            }
        }
    }

    /**
     * @param DOMElement  $node
     * @param string      $name
     * @param mixed|null  $data
     * @param string|null $documentID
     * @return mixed
     */
    public static function data($node, $name, $data, $documentID = null)
    {
        $documentID = $documentID ?? self::getDocumentID($node);
        $document   = self::$documents[$documentID];
        $node       = self::dataSetupNode($node, $documentID);
        if (!isset($node->dataID)) {
            $node->dataID = ++self::$documents[$documentID]->uuid;
        }
        $id = $node->dataID;
        if (!isset($document->data[$id])) {
            $document->data[$id] = [];
        }
        if ($data !== null) {
            $document->data[$id][$name] = $data;
        }
        if ($name && isset($document->data[$id][$name])) {
            return $document->data[$id][$name];
        }

        return $id;
    }

    /**
     * @param DOMElement $node
     * @param string     $name
     * @param string     $documentID
     */
    public static function removeData($node, $name, $documentID): void
    {
        $documentID = $documentID ?? self::getDocumentID($node);
        $document   = self::$documents[$documentID];
        $node       = self::dataSetupNode($node, $documentID);
        $id         = $node->dataID;
        if ($name) {
            if (isset($document->data[$id][$name])) {
                unset($document->data[$id][$name]);
            }
            $name = null;
            foreach ($document->data[$id] as $name) {
                break;
            }
            if (!$name) {
                self::removeData($node, $name, $documentID);
            }
        } else {
            self::dataRemoveNode($node, $documentID);
        }
    }
}

phpQuery::$plugins = new phpQueryPlugins();
