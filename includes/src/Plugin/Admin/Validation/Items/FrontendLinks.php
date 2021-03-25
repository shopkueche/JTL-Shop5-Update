<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\Admin\Validation\ValidationItemInterface;
use JTL\Plugin\InstallCode;

/**
 * Class FrontendLinks
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class FrontendLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['FrontendLink'][0])) {
            return InstallCode::OK;
        }
        $node   = $node['FrontendLink'][0]['Link'] ?? null;
        $tplDir = $dir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE;
        if (!GeneralObject::hasCount($node)) {
            return InstallCode::MISSING_FRONTEND_LINKS;
        }
        foreach ($node as $i => $link) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (!\is_array($link)) {
                return InstallCode::MISSING_FRONTEND_LINKS;
            }
            $link = $this->sanitizeLinkData($link);
            if (\mb_strlen($link['Filename']) === 0
                && $this->getContext() !== ValidationItemInterface::CONTEXT_PLUGIN
            ) {
                return InstallCode::INVALID_FRONTEND_LINK_FILENAME;
            }
            \preg_match(
                '/[\w\- ]+/u',
                $link['Name'],
                $hits1
            );
            $len = \mb_strlen($link['Name']);
            if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                return InstallCode::INVALID_FRONTEND_LINK_NAME;
            }
            // Templatename UND Fullscreen Templatename vorhanden?
            // Es darf nur entweder oder geben
            if (isset($link['Template'], $link['FullscreenTemplate'])
                && \mb_strlen($link['Template']) > 0
                && \mb_strlen($link['FullscreenTemplate']) > 0
            ) {
                return InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES;
            }
            if (!isset($link['FullscreenTemplate']) || \mb_strlen($link['FullscreenTemplate']) === 0) {
                if (!isset($link['Template']) || \mb_strlen($link['Template']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/', $link['Template'], $hits1);
                if (\mb_strlen($hits1[0]) === \mb_strlen($link['Template'])) {
                    if (!\file_exists($tplDir . $link['Template'])) {
                        return InstallCode::MISSING_FRONTEND_LINK_TEMPLATE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE;
                }
            }
            if (!isset($link['Template']) || \mb_strlen($link['Template']) === 0) {
                if (\mb_strlen($link['FullscreenTemplate']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/', $link['FullscreenTemplate'], $hits1);
                if (\mb_strlen($hits1[0]) === \mb_strlen($link['FullscreenTemplate'])) {
                    if (!\file_exists($tplDir . $link['FullscreenTemplate'])) {
                        return InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME;
                }
            }
            if (isset($link['VisibleAfterLogin']) && !\in_array($link['VisibleAfterLogin'], ['Y', 'N'], true)) {
                return InstallCode::INVALID_FRONEND_LINK_VISIBILITY;
            }
            if (isset($link['PrintButton']) && !\in_array($link['PrintButton'], ['Y', 'N'], true)) {
                return InstallCode::INVALID_FRONEND_LINK_PRINT;
            }
            if (isset($link['NoFollow']) && !\in_array($link['NoFollow'], ['Y', 'N'], true)) {
                return InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW;
            }
            if (empty($link['LinkLanguage']) || !\is_array($link['LinkLanguage'])) {
                return InstallCode::INVALID_FRONEND_LINK_ISO;
            }
            foreach ($link['LinkLanguage'] as $l => $localized) {
                $l         = (string)$l;
                $localized = $this->sanitizeLocalizationData($localized);
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                    $len = \mb_strlen($localized['iso']);
                    if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_ISO;
                    }
                } elseif (\mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    \preg_match('/[a-zA-Z0-9- ]+/', $localized['Seo'], $hits1);
                    $len = \mb_strlen($localized['Seo']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_SEO;
                    }
                    \preg_match(
                        '/[\w\- ]+/u',
                        $localized['Name'],
                        $hits1
                    );
                    $len = \mb_strlen($localized['Name']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_NAME;
                    }
                    \preg_match(
                        '/[\w\,\.\;\- ]+/u',
                        $localized['Title'],
                        $hits1
                    );
                    $len = \mb_strlen($localized['Title']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_TITLE;
                    }
                    \preg_match(
                        '/[\w\,\.\;\- ]+/u',
                        $localized['MetaTitle'],
                        $hits1
                    );
                    $len = \mb_strlen($localized['MetaTitle']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_TITLE;
                    }
                    \preg_match(
                        '/[\w,\- ]+/u',
                        $localized['MetaKeywords'],
                        $hits1
                    );
                    $len = \mb_strlen($localized['MetaKeywords']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS;
                    }
                    \preg_match(
                        '/[\w\,\.\;\- ]+/u',
                        $localized['MetaDescription'],
                        $hits1
                    );
                    $len = \mb_strlen($localized['MetaDescription']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $link
     * @return array
     */
    private function sanitizeLinkData(array $link): array
    {
        $link['Filename'] = $link['Filename'] ?? '';
        $link['Name']     = $link['Name'] ?? '';

        return $link;
    }

    /**
     * @param array $localized
     * @return array
     */
    private function sanitizeLocalizationData(array $localized): array
    {
        $localized['iso']             = $localized['iso'] ?? '';
        $localized['MetaDescription'] = $localized['MetaDescription'] ?? '';
        $localized['MetaKeywords']    = $localized['MetaKeywords'] ?? '';
        $localized['MetaTitle']       = $localized['MetaTitle'] ?? '';
        $localized['Name']            = $localized['Name'] ?? '';
        $localized['Seo']             = $localized['Seo'] ?? '';
        $localized['Title']           = $localized['Title'] ?? '';

        return $localized;
    }
}
