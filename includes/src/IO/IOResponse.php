<?php

namespace JTL\IO;

use Exception;
use JsonSerializable;
use JTL\Helpers\Text;

/**
 * Class IOResponse
 * @package JTL\IO
 */
class IOResponse implements JsonSerializable
{
    /**
     * @var array
     */
    private $domAssigns = [];

    /**
     * @var array
     */
    private $varAssigns = [];

    /**
     * @var array
     * @deprecated since 5.0.0
     */
    private $scripts = [];

    /**
     * @var string[]
     */
    private $debugLogLines = [];

    /**
     * @var null|string
     */
    private $windowLocationHref;

    /**
     * @var array
     */
    private $evoProductFunctionCalls = [];

    /**
     * @param $target
     * @param $attr
     * @param $data
     * @return $this
     * @deprecated since 5.0.0
     */
    public function assign($target, $attr, $data): self
    {
        return $this->assignDom($target, $attr, $data);
    }

    /**
     * @param string $target
     * @param string $attr
     * @param mixed  $data
     * @return $this
     */
    public function assignDom($target, $attr, $data): self
    {
        $this->domAssigns[] = (object)[
            'target' => $target,
            'attr'   => $attr,
            'data'   => $data
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function assignVar(string $name, $value): self
    {
        $this->varAssigns[] = (object)[
            'name'  => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setClientRedirect(string $url): self
    {
        $this->windowLocationHref = $url;

        return $this;
    }

    /**
     * @param $msg
     * @param bool $groupHead
     * @param bool $groupEnd
     * @return $this
     */
    public function debugLog($msg, bool $groupHead = false, $groupEnd = false): self
    {
        $this->debugLogLines[] = [$msg, $groupHead, $groupEnd];

        return $this;
    }

    /**
     * @param string $js
     * @return $this
     * @deprecated since 5.0.0
     */
    public function script($js): self
    {
        $this->scripts[] = $js;

        return $this;
    }

    /**
     * @param string $name
     * @param $args
     * @return $this
     */
    public function callEvoProductFunction($name, ...$args): self
    {
        $this->evoProductFunctionCalls[] = [$name, $args];

        if (\defined('IO_LOG_CONSOLE') && \IO_LOG_CONSOLE === true) {
            $reset  = 'background: transparent; color: #000;';
            $orange = 'background: #e86c00; color: #fff;';
            $grey   = 'background: #e8e8e8; color: #333;';

            $this->debugLog(['%c CALL %c ' . $name, $orange, $reset], false, false);
            $this->debugLog(['%c PARAMS %c', $grey, $reset, $args], false, false);
            $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset], true, false);

            foreach ($this->generateCallTrace() as $trace) {
                $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset, $trace], false, false);
            }

            $this->debugLog(null, false, true);
            $this->debugLog(null, false, true);
        }

        return $this;
    }

    /**
     * @param string $function
     * @return $this
     * @deprecated since 5.0.0
     */
    public function jsfunc($function): self
    {
        $arguments = \func_get_args();
        \array_shift($arguments);

        $filtered = $arguments;

        \array_walk($filtered, static function (&$value, $key) {
            switch (\gettype($value)) {
                case 'array':
                case 'object':
                case 'string':
                    $value = Text::utf8_convert_recursive($value);
                    $value = \json_encode($value);
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'integer':
                case 'double':
                    // nothing todo
                    break;

                case 'resource':
                case 'NULL':
                case 'unknown type':
                default:
                    $value = 'null';
                    break;
            }
        });

        $argumentlist = \implode(', ', $filtered);
        $syntax       = \sprintf('%s(%s);', $function, $argumentlist);

        $this->script($syntax);

        if (\defined('IO_LOG_CONSOLE') && \IO_LOG_CONSOLE === true) {
            $reset  = 'background: transparent; color: #000;';
            $orange = 'background: #e86c00; color: #fff;';
            $grey   = 'background: #e8e8e8; color: #333;';

            $args = Text::utf8_convert_recursive($arguments);

            $this->debugLog(['%c CALL %c {$function}()', $orange, $reset], false, false);
            $this->debugLog(['%c METHOD %c {$function}()', $grey, $reset], false, false);
            $this->debugLog(['%c PARAMS %c', $grey, $reset, $args], false, false);
            $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset], true, false);

            foreach ($this->generateCallTrace() as $trace) {
                $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset, $trace], false, false);
            }

            $this->debugLog(null, false, true);
            $this->debugLog(null, false, true);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function generateCallTrace(): array
    {
        $str   = (new Exception())->getTraceAsString();
        $trace = \explode("\n", $str);
        $trace = \array_reverse($trace);
        \array_shift($trace);
        \array_pop($trace);
        $result = [];

        foreach ($trace as $i => $t) {
            $result[] = '#' . ($i + 1) . \mb_substr($t, \mb_strpos($t, ' '));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'js'                 => $this->scripts,
            'domAssigns'         => $this->domAssigns,
            'varAssigns'         => $this->varAssigns,
            'windowLocationHref' => $this->windowLocationHref,
            'debugLogLines'      => $this->debugLogLines,
            'evoProductCalls'    => $this->evoProductFunctionCalls,
        ];
    }
}
