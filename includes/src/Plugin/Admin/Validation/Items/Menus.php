<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\InstallCode;

/**
 * Class Menus
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Menus extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['Adminmenu'][0])) {
            return InstallCode::OK;
        }
        $node = $node['Adminmenu'][0];
        if (GeneralObject::hasCount('Customlink', $node)) {
            $this->validateCustomLinks($node['Customlink'], $dir);
        }
        if (!GeneralObject::isCountable('Settingslink', $node)) {
            return InstallCode::OK;
        }

        return $this->validateSettingsLinks($node['Settingslink'], $dir);
    }

    /**
     * @param array  $customLinks
     * @param string $dir
     * @return int
     */
    private function validateCustomLinks(array $customLinks, string $dir): int
    {
        foreach ($customLinks as $i => $customLink) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                \preg_match(
                    '/[\w\- ]+/u',
                    $customLink['Name'],
                    $hits
                );
                if (empty($customLink['Name']) || \mb_strlen($hits[0]) !== \mb_strlen($customLink['Name'])) {
                    return InstallCode::INVALID_CUSTOM_LINK_NAME;
                }
                if (isset($customLink['Filename'])) {
                    if (empty($customLink['Filename'])) {
                        return InstallCode::INVALID_CUSTOM_LINK_FILE_NAME;
                    }
                    if (!\file_exists($dir . \PFAD_PLUGIN_ADMINMENU . $customLink['Filename'])) {
                        return InstallCode::MISSING_CUSTOM_LINK_FILE;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $settingsLinks
     * @param string $dir
     * @return int
     */
    private function validateSettingsLinks(array $settingsLinks, string $dir): int
    {
        foreach ($settingsLinks as $i => $settingsLink) {
            $i            = (string)$i;
            $settingsLink = $this->sanitizeSettingsLink($settingsLink);
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (empty($settingsLink['Name'])) {
                return InstallCode::INVALID_CONFIG_LINK_NAME;
            }
            if (empty($settingsLink['Setting']) || !\is_array($settingsLink['Setting'])) {
                return InstallCode::MISSING_CONFIG;
            }
            if (($res = $this->validateSettings($settingsLink['Setting'], $dir)) !== InstallCode::OK) {
                return $res;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $settings
     * @param string $dir
     * @return int
     */
    private function validateSettings(array $settings, string $dir): int
    {
        $type = '';
        foreach ($settings as $j => $setting) {
            if (!\is_array($setting)) {
                return InstallCode::MISSING_CONFIG;
            }
            $j       = (string)$j;
            $setting = $this->sanitizeSetting($setting);
            \preg_match('/[0-9]+\sattr/', $j, $hits3);
            \preg_match('/[0-9]+/', $j, $hits4);

            if (isset($hits3[0]) && \mb_strlen($hits3[0]) === \mb_strlen($j)) {
                $type = $setting['type'];
                if (\mb_strlen($type) === 0) {
                    return InstallCode::INVALID_CONFIG_TYPE;
                }
                if (!isset($setting['sort']) || \mb_strlen($setting['sort']) === 0) {
                    return InstallCode::INVALID_CONFIG_SORT_VALUE;
                }
                if (!isset($setting['conf']) || \mb_strlen($setting['conf']) === 0) {
                    return InstallCode::INVALID_CONF;
                }
            } elseif (\mb_strlen($hits4[0]) === \mb_strlen($j)) {
                if (!isset($setting['Name']) || \mb_strlen($setting['Name']) === 0) {
                    return InstallCode::INVALID_CONFIG_NAME;
                }
                if (!\is_string($setting['ValueName']) || \mb_strlen($setting['ValueName']) === 0) {
                    return InstallCode::INVALID_CONF_VALUE_NAME;
                }
                if ($type === InputType::SELECT) {
                    if (($res = $this->validateSelect($setting, $dir)) !== InstallCode::OK) {
                        return $res;
                    }
                } elseif ($type === InputType::RADIO) {
                    if (($res = $this->validateRadio($setting)) !== InstallCode::OK) {
                        return $res;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $setting
     * @param string $dir
     * @return int
     */
    private function validateSelect(array $setting, string $dir): int
    {
        if (!empty($setting['OptionsSource']) && \is_array($setting['OptionsSource'])) {
            if (empty($setting['OptionsSource'][0]['File'])) {
                return InstallCode::INVALID_OPTIONS_SOURE_FILE;
            }
            if (!\file_exists($dir . \PFAD_PLUGIN_ADMINMENU . $setting['OptionsSource'][0]['File'])) {
                return InstallCode::MISSING_OPTIONS_SOURE_FILE;
            }
        } elseif (GeneralObject::hasCount('SelectboxOptions', $setting)) {
            if (\count($setting['SelectboxOptions'][0]) === 1) {
                foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $option) {
                    $y = (string)$y;
                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                    \preg_match('/[0-9]+/', $y, $hits7);

                    if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                        if (\mb_strlen($option['value']) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                        if (\mb_strlen($option['sort']) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                    } elseif (\mb_strlen($hits7[0]) === \mb_strlen($y)) {
                        if (\mb_strlen($option) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                    }
                }
            } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
                if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
                if (\mb_strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
            }
        } else {
            return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
        }

        return InstallCode::OK;
    }

    /**
     * @param array $setting
     * @return int
     */
    private function validateRadio(array $setting): int
    {
        if (!empty($setting['OptionsSource']) && \is_array($setting['OptionsSource'])) {
            return InstallCode::OK;
        }
        if (GeneralObject::hasCount('RadioOptions', $setting)) {
            if (\count($setting['RadioOptions'][0]) === 1) {
                foreach ($setting['RadioOptions'][0]['Option'] as $y => $option) {
                    $y = (string)$y;
                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                    \preg_match('/[0-9]+/', $y, $hits7);
                    if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                        if (\mb_strlen($option['value']) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                        if (\mb_strlen($option['sort']) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                    } elseif (\mb_strlen($hits7[0]) === \mb_strlen($y)) {
                        if (\mb_strlen($option) === 0) {
                            return InstallCode::INVALID_CONFIG_OPTION;
                        }
                    }
                }
            } elseif (\count($setting['RadioOptions'][0]) === 2) {
                if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
                if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
                if (\mb_strlen($setting['RadioOptions'][0]['Option']) === 0) {
                    return InstallCode::INVALID_CONFIG_OPTION;
                }
            }

            return InstallCode::OK;
        }

        return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
    }

    /**
     * @param array $setting
     * @return array
     */
    private function sanitizeSetting(array $setting): array
    {
        $setting['Name']      = $setting['Name'] ?? '';
        $setting['ValueName'] = $setting['ValueName'] ?? '';
        $setting['type']      = $setting['type'] ?? '';

        return $setting;
    }

    /**
     * @param array $settingsLink
     * @return array
     */
    private function sanitizeSettingsLink(array $settingsLink): array
    {
        return $settingsLink;
    }
}
