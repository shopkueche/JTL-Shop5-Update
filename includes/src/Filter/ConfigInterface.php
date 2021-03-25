<?php declare(strict_types=1);

namespace JTL\Filter;

/**
 * Interface ConfigInterface
 * @package JTL\Filter
 */
interface ConfigInterface
{
    /**
     * @return ConfigInterface
     */
    public static function getDefault(): self;

    /**
     * @return int
     */
    public function getLanguageID(): int;

    /**
     * @param int $langID
     */
    public function setLanguageID(int $langID): void;

    /**
     * @return array
     */
    public function getLanguages(): array;

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages);

    /**
     * @param string|null $section
     * @return array|string|int
     */
    public function getConfig($section = null);

    /**
     * @param array $config
     */
    public function setConfig(array $config): void;

    /**
     * @return int
     */
    public function getCustomerGroupID(): int;

    /**
     * @param int $customerGroupID
     */
    public function setCustomerGroupID(int $customerGroupID): void;

    /**
     * @return string
     */
    public function getBaseURL(): string;

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL);
}
