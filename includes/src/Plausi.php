<?php

namespace JTL;

/**
 * Class Plausi
 * @package JTL
 */
class Plausi
{
    /**
     * @var array
     */
    protected $xPostVar_arr = [];

    /**
     * @var array
     */
    protected $xPlausiVar_arr = [];

    /**
     * @return array
     */
    public function getPostVar(): array
    {
        return $this->xPostVar_arr;
    }

    /**
     * @return array
     */
    public function getPlausiVar(): array
    {
        return $this->xPlausiVar_arr;
    }

    /**
     * @param array      $variables
     * @param array|null $hasHTML
     * @param bool       $toEntities
     * @return bool
     */
    public function setPostVar($variables, $hasHTML = null, bool $toEntities = false): bool
    {
        if (\is_array($variables) && \count($variables) > 0) {
            if (\is_array($hasHTML)) {
                $excludeKeys = \array_fill_keys($hasHTML, 1);
                $filter      = \array_diff_key($variables, $excludeKeys);
                $excludes    = \array_intersect_key($variables, $excludeKeys);
                if ($toEntities) {
                    \array_map('\htmlentities', $excludes);
                }
                $this->xPostVar_arr = \array_merge($variables, $filter, $excludes);
            } else {
                $this->xPostVar_arr = $variables;
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $variables
     * @return bool
     */
    public function setPlausiVar($variables): bool
    {
        if (!\is_array($variables) || \count($variables) === 0) {
            return false;
        }
        $this->xPlausiVar_arr = $variables;

        return true;
    }

    /**
     * @param null $type
     * @param bool $update
     * @return bool
     */
    public function doPlausi($type = null, bool $update = false): bool
    {
        return false;
    }
}
