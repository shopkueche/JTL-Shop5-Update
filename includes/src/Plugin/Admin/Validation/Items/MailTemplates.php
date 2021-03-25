<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;

/**
 * Class MailTemplates
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class MailTemplates extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        if (!GeneralObject::isCountable('Emailtemplate', $node)) {
            return InstallCode::OK;
        }
        $node = $node['Emailtemplate'][0]['Template'] ?? null;
        if (!GeneralObject::hasCount($node)) {
            return InstallCode::MISSING_EMAIL_TEMPLATES;
        }
        foreach ($node as $i => $tpl) {
            if (!\is_array($tpl)) {
                continue;
            }
            $tpl = $this->sanitizeTemplate($tpl);
            $i   = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            \preg_match(
                '/[\w\/\- ]+/u',
                $tpl['Name'],
                $hits1
            );
            if (\mb_strlen($hits1[0]) !== \mb_strlen($tpl['Name'])) {
                return InstallCode::INVALID_TEMPLATE_NAME;
            }
            if ($tpl['Type'] !== 'text/html' && $tpl['Type'] !== 'text') {
                return InstallCode::INVALID_TEMPLATE_TYPE;
            }
            if (\mb_strlen($tpl['ModulId']) === 0) {
                return InstallCode::INVALID_TEMPLATE_MODULE_ID;
            }
            if (\mb_strlen($tpl['Active']) === 0) {
                return InstallCode::INVALID_TEMPLATE_ACTIVE;
            }
            if (\mb_strlen($tpl['AKZ']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AKZ;
            }
            if (\mb_strlen($tpl['AGB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AGB;
            }
            if (\mb_strlen($tpl['WRB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_WRB;
            }
            if (empty($tpl['TemplateLanguage']) || !\is_array($tpl['TemplateLanguage'])) {
                return InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE;
            }
            if (($res = $this->validateLocalization($tpl['TemplateLanguage'])) !== InstallCode::OK) {
                return $res;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $localization
     * @return int
     */
    private function validateLocalization(array $localization): int
    {
        foreach ($localization as $l => $localized) {
            if (!\is_array($localized)) {
                continue;
            }
            $localized = $this->sanitizeLocalization($localized);
            $l         = (string)$l;
            \preg_match('/[0-9]+\sattr/', $l, $hits1);
            \preg_match('/[0-9]+/', $l, $hits2);
            if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                $len = \mb_strlen($localized['iso']);
                if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                    return InstallCode::INVALID_EMAIL_TEMPLATE_ISO;
                }
            } elseif (\mb_strlen($hits2[0]) === \mb_strlen($l)) {
                \preg_match('/[a-zA-Z0-9\/_\-.#: ]+/', $localized['Subject'], $hits1);
                $len = \mb_strlen($localized['Subject']);
                if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                    return InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $tpl
     * @return array
     */
    private function sanitizeTemplate(array $tpl): array
    {
        $tpl['Name']    = $tpl['Name'] ?? '';
        $tpl['Type']    = $tpl['Type'] ?? '';
        $tpl['ModulId'] = $tpl['ModulId'] ?? '';
        $tpl['Active']  = $tpl['Active'] ?? '';
        $tpl['AKZ']     = $tpl['AKZ'] ?? '';
        $tpl['AGB']     = $tpl['AGB'] ?? '';
        $tpl['WRB']     = $tpl['WRB'] ?? '';

        return $tpl;
    }

    /**
     * @param array $localized
     * @return array
     */
    private function sanitizeLocalization(array $localized): array
    {
        $localized['Subject'] = $localized['Subject'] ?? '';

        return $localized;
    }
}
