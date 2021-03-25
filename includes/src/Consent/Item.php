<?php declare(strict_types=1);

namespace JTL\Consent;

use JTL\Model\DataModelInterface;
use JTL\Shop;

/**
 * Class Item
 * @package JTL\Consent
 */
class Item implements ItemInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $pluginID;

    /**
     * @var string
     */
    private $itemID;

    /**
     * @var string[]
     */
    private $name;

    /**
     * @var string[]
     */
    private $description;

    /**
     * @var string[]
     */
    private $purpose;

    /**
     * @var string[]
     */
    private $company;

    /**
     * @var string[]
     */
    private $privacyPolicy;

    /**
     * @var int
     */
    private $currentLanguageID;

    /**
     * @var bool
     */
    private $active;

    /**
     * Item constructor.
     * @param int|null $currentLanguageID
     */
    public function __construct(int $currentLanguageID = null)
    {
        $this->currentLanguageID = $currentLanguageID ?? Shop::getLanguageID();
    }

    /**
     * @param ConsentModel|DataModelInterface $model
     * @return $this
     */
    public function loadFromModel(DataModelInterface $model): self
    {
        $this->setID($model->getId());
        $this->setItemID($model->getItemID());
        $this->setCompany($model->getCompany());
        $this->setPluginID($model->getPluginID());
        $this->setActive($model->getActive() === 1);
        foreach ($model->getLocalization() as $localization) {
            /** @var ConsentLocalizationModel $localization */
            $langID = $localization->getLanguageID();
            $this->setName($localization->getName(), $langID);
            $this->setPrivacyPolicy($localization->getPrivacyPolicy(), $langID);
            $this->setDescription($localization->getDescription(), $langID);
            $this->setPurpose($localization->getPurpose(), $langID);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID($id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getName(int $idx = null): string
    {
        return $this->name[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->name[$idx ?? $this->currentLanguageID] = $name;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(int $idx = null): string
    {
        return $this->description[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description, int $idx = null): void
    {
        $this->description[$idx ?? $this->currentLanguageID] = $description;
    }

    /**
     * @inheritDoc
     */
    public function getPurpose(int $idx = null): string
    {
        return $this->purpose[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setPurpose(string $purpose, int $idx = null): void
    {
        $this->purpose[$idx ?? $this->currentLanguageID] = $purpose;
    }

    /**
     * @inheritDoc
     */
    public function getCompany(int $idx = null): string
    {
        return $this->company[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setCompany(string $company, int $idx = null): void
    {
        $this->company[$idx ?? $this->currentLanguageID] = $company;
    }

    /**
     * @inheritDoc
     */
    public function getPrivacyPolicy(int $idx = null): string
    {
        return $this->privacyPolicy[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setPrivacyPolicy(string $tos, int $idx = null): void
    {
        $this->privacyPolicy[$idx ?? $this->currentLanguageID] = $tos;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentLanguageID(): int
    {
        return $this->currentLanguageID;
    }

    /**
     * @inheritDoc
     */
    public function setCurrentLanguageID(int $currentLanguageID): void
    {
        $this->currentLanguageID = $currentLanguageID;
    }

    /**
     * @return string
     */
    public function getItemID(): string
    {
        return $this->itemID;
    }

    /**
     * @param string $itemID
     */
    public function setItemID(string $itemID): void
    {
        $this->itemID = $itemID;
    }

    /**
     * @return int
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
