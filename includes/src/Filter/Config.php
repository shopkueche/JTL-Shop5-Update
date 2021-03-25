<?php declare(strict_types=1);

namespace JTL\Filter;

use JTL\Language\LanguageHelper;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * Class Config
 * @package JTL\Filter
 */
class Config implements ConfigInterface
{
    /**
     * @var int
     */
    private $langID = 0;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @var string
     */
    private $baseURL = '';

    /**
     * @inheritdoc
     */
    public static function getDefault(): ConfigInterface
    {
        $config = new self();
        $config->setLanguageID(Shop::getLanguageID());
        $config->setLanguages(LanguageHelper::getInstance()->getLangArray());
        $config->setConfig(Shopsetting::getInstance()->getAll());
        $config->setCustomerGroupID(Frontend::getCustomerGroup()->getID());
        $config->setBaseURL(Shop::getURL() . '/');

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->langID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $langID): void
    {
        $this->langID = $langID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @inheritdoc
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @inheritdoc
     */
    public function getConfig($section = null)
    {
        return $section === null ? $this->config : $this->config[$section];
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupID(int $customerGroupID): void
    {
        $this->customerGroupID = $customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @inheritdoc
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }
}
