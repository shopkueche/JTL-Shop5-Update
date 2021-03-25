<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc;

/**
 * Interface ComponentPropertyInterface
 * @package scc
 */
interface ComponentPropertyInterface
{
    /**
     * @return bool
     */
    public function getIsRequired(): bool;

    /**
     * @param bool $required
     */
    public function setIsRequired(bool $required): void;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * @return bool
     */
    public function hasValue(): bool;
}
