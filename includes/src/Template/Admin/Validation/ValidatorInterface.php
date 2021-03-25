<?php declare(strict_types=1);

namespace JTL\Template\Admin\Validation;

/**
 * Interface ValidatorInterface
 * @package JTL\Template\Admin\Validation
 */
interface ValidatorInterface
{
    /**
     * @return string
     */
    public function getDir(): string;

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void;

    /**
     * @param string $path
     * @param bool   $forUpdate
     * @return int
     */
    public function validateByPath(string $path, bool $forUpdate = false): int;
}
