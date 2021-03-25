<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc;

/**
 * Interface ComponentRendererInterface
 * @package scc
 */
interface ComponentRendererInterface
{
    /**
     * @param array $params
     * @param mixed ...$args
     * @return string
     */
    public function render(array $params, ...$args): string;

    /**
     * init default values
     */
    public function preset(): void;
}
