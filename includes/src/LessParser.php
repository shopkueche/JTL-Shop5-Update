<?php

namespace JTL;

/**
 * Class LessParser
 * @package JTL
 */
class LessParser
{
    /**
     * @var array
     */
    private $stack = [];

    /**
     * @param string $file
     * @return $this
     */
    public function read($file): self
    {
        $lines = \file($file, \FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (\preg_match('/@([\d\w\-]+)\s*:\s*([^;]+)/', $line, $matches)) {
                [, $key, $value]   = $matches;
                $this->stack[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $file
     * @return bool|int
     */
    public function write($file)
    {
        $content = '';
        foreach ($this->stack as $key => $value) {
            $content .= '@' . $key . ': ' . $value . ";\r\n";
        }

        return \file_put_contents($file, $content);
    }

    /**
     * @return array
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * @return array
     */
    public function getColors(): array
    {
        $colors = [];
        foreach ($this->stack as $key => $value) {
            $color = $this->getAs($value, 'color');
            if ($color) {
                $colors[$key] = $color;
            }
        }

        return $colors;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value): self
    {
        $this->stack[$key] = $value;

        return $this;
    }

    /**
     * @param string      $key
     * @param null|string $type
     * @return bool|null
     */
    public function get($key, $type = null)
    {
        $value = $this->stack[$key] ?? null;

        if ($value !== null && !$type !== null) {
            $typedValue = $this->getAs($value, $type);
            if ($typedValue !== false) {
                return $typedValue;
            }
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $type
     * @return bool|string
     */
    protected function getAs($value, $type)
    {
        $matches = [];

        switch (\mb_convert_case($type, \MB_CASE_LOWER)) {
            case 'color':
                // rgb(255,255,255)
                if (\preg_match('/rgb(\s*)\(([\d\s]+),([\d\s]+),([\d\s]+)\)/', $value, $matches)) {
                    return $this->rgb2html((int)$matches[2], (int)$matches[3], (int)$matches[4]);
                } // #fff or #ffffff
                if (\preg_match('/#([\w\d]+)/', $value, $matches)) {
                    return \trim($matches[0]);
                }
                break;

            case 'size':
                // 1.2em 15% '12 px'
                if (\preg_match('/([\d\.]+)(.*)/', $value, $matches)) {
                    $pair = [
                        'numeric' => (float)$matches[1],
                        'unit'    => \trim($matches[2])
                    ];

                    return $pair['numeric'];
                }
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * @param int|array $r
     * @param int       $g
     * @param int       $b
     * @return string
     */
    protected function rgb2html($r, $g, $b): string
    {
        if (\is_array($r) && \count($r) === 3) {
            [$r, $g, $b] = $r;
        }

        $r = (int)$r;
        $g = (int)$g;
        $b = (int)$b;

        $r = \dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = \dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = \dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color  = (\mb_strlen($r) < 2 ? '0' : '') . $r;
        $color .= (\mb_strlen($g) < 2 ? '0' : '') . $g;
        $color .= (\mb_strlen($b) < 2 ? '0' : '') . $b;

        return '#' . $color;
    }
}
