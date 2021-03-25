<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Consent
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Consent extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        if (!isset($node['ServicesRequiringConsent'][0])) {
            return InstallCode::OK;
        }
        $node = $node['ServicesRequiringConsent'][0]['Vendor'] ?? null;
        foreach ($node as $i => $vendor) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (!\is_array($vendor)) {
                return InstallCode::MISSING_CONSENT_VENDOR;
            }
            $vendor = $this->sanitizeVendorData($vendor);
            if (\mb_strlen($vendor['ID']) === 0) {
                return InstallCode::INVALID_CONSENT_VENDOR_ID;
            }
            if (\mb_strlen($vendor['Company']) === 0) {
                return InstallCode::INVALID_CONSENT_VENDOR_COMPANY;
            }
            if (empty($vendor['Localization']) || !\is_array($vendor['Localization'])) {
                return InstallCode::INVALID_CONSENT_VENDOR_LOCALIZATION;
            }
            foreach ($vendor['Localization'] as $l => $localized) {
                $l         = (string)$l;
                $localized = $this->sanitizeLocalizationData($localized);
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                    $len = \mb_strlen($localized['iso']);
                    if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_CONSENT_VENDOR_LOCALIZATION_ISO;
                    }
                } else {
                    if (\mb_strlen($localized['Name']) === 0) {
                        return InstallCode::INVALID_CONSENT_VENDOR_NAME;
                    }
                    if (\mb_strlen($localized['Purpose']) === 0) {
                        return InstallCode::INVALID_CONSENT_VENDOR_PURPOSE;
                    }
                    if (\mb_strlen($localized['Description']) === 0) {
                        return InstallCode::INVALID_CONSENT_VENDOR_DESCRIPTION;
                    }
                    if (\mb_strlen($localized['PrivacyPolicy']) === 0) {
                        return InstallCode::INVALID_CONSENT_VENDOR_PRIV_POL;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $vendor
     * @return array
     */
    private function sanitizeVendorData(array $vendor): array
    {
        $vendor['ID']      = $vendor['ID'] ?? '';
        $vendor['Company'] = $vendor['Company'] ?? '';

        return $vendor;
    }

    /**
     * @param array $localized
     * @return array
     */
    private function sanitizeLocalizationData(array $localized): array
    {
        $localized['iso']           = $localized['iso'] ?? '';
        $localized['Name']          = $localized['Name'] ?? '';
        $localized['Purpose']       = $localized['Purpose'] ?? '';
        $localized['Description']   = $localized['Description'] ?? '';
        $localized['PrivacyPolicy'] = $localized['PrivacyPolicy'] ?? '';

        return $localized;
    }
}
