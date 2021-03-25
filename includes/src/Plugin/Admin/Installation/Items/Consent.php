<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class Consent
 * @package JTL\Plugin\Admin\Installation\Items
 */
class Consent extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['ServicesRequiringConsent'][0]['Vendor'])
        && \is_array($this->baseNode['Install'][0]['ServicesRequiringConsent'][0]['Vendor'])
            ? $this->baseNode['Install'][0]['ServicesRequiringConsent'][0]['Vendor']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $pluginID = $this->plugin->kPlugin;
        foreach ($this->getNode() as $i => $vendor) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            $consentID = $this->addVendor($pluginID, $vendor);
            if ($consentID <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_VENDOR;
            }
            $allLanguages            = \collect(LanguageHelper::getAllLanguages(1));
            $defaultLanguage         = LanguageHelper::getDefaultLanguage();
            $localization            = new stdClass();
            $localization->consentID = $consentID;
            $defaultLocalization     = null;
            $addedLanguages          = [];
            foreach ($vendor['Localization'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    $langCode = \mb_convert_case($localized['iso'], \MB_CASE_LOWER);
                    $mapped   = LanguageHelper::getLangIDFromIso($langCode);
                    if ($mapped === null) {
                        $localization->languageID = 0;
                        continue;
                    }
                    $localization->languageID = $mapped->kSprache;
                    $addedLanguages[]         = $mapped->kSprache;
                } elseif ($localization->languageID > 0 && \mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    $localization->name          = $localized['Name'];
                    $localization->purpose       = $localized['Purpose'];
                    $localization->description   = $localized['Description'];
                    $localization->privacyPolicy = $localized['PrivacyPolicy'];
                    $this->db->insert('tconsentlocalization', $localization);
                    if ($defaultLocalization === null || $localization->languageID === $defaultLanguage->getId()) {
                        $defaultLocalization = clone $localization;
                    }
                }
            }
            $missingLanguages = $allLanguages->filter(static function (LanguageModel $e) use ($addedLanguages) {
                return !\in_array($e->getId(), $addedLanguages, true);
            });
            $this->addMissingTranslations($missingLanguages->toArray(), $defaultLocalization);
        }

        return InstallCode::OK;
    }

    /**
     * @param LanguageModel[] $missingLanguages
     * @param stdClass        $defaultLocalization
     */
    private function addMissingTranslations(array $missingLanguages, stdClass $defaultLocalization): void
    {
        foreach ($missingLanguages as $language) {
            $defaultLocalization->languageID = $language->getId();
            $this->db->insert('tconsentlocalization', $defaultLocalization);
        }
    }

    /**
     * @param int   $pluginID
     * @param array $items
     * @return int
     */
    private function addVendor(int $pluginID, array $items): int
    {
        $item           = new stdClass();
        $item->itemID   = $items['ID'];
        $item->company  = $items['Company'];
        $item->pluginID = $pluginID;
        $item->active   = 1;

        return $this->db->insert('tconsent', $item);
    }
}
