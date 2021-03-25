<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc;

use scc\exceptions\ComponentAlreadyRegisteredException;

/**
 * Interface RendererInterface
 * @package scc
 */
interface RendererInterface
{
    /**
     * @param ComponentInterface $component
     * @throws ComponentAlreadyRegisteredException
     */
    public function registerComponent(ComponentInterface $component): void;

    /**
     * @param string $type
     * @param string $name
     */
    public function unregisterComponent(string $type, string $name): void;

    /**
     * @param string $type
     * @return array
     */
    public function getRegisteredComponents(string $type): array;
}
