<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;

/**
 * Interface TemplateServiceInterface
 * @package JTL\Template
 */
interface TemplateServiceInterface
{
    /**
     *
     */
    public function save(): void;

    /**
     * @param bool $withLicense
     * @return Model
     * @throws Exception
     */
    public function getActiveTemplate(bool $withLicense = true): Model;

    /**
     * @param array $attributes
     * @param bool  $withLicense
     * @return Model
     * @throws Exception
     */
    public function loadFull(array $attributes, bool $withLicense = true): Model;
}
