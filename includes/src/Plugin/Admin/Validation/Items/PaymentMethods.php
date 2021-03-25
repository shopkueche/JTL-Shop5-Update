<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\Admin\InputType;
use JTL\Plugin\InstallCode;

/**
 * Class PaymentMethods
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class PaymentMethods extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode()['PaymentMethod'][0]['Method'] ?? null;
        $dir  = $this->getDir();
        if (!GeneralObject::isCountable($node)) {
            return InstallCode::OK;
        }
        $tsCodes = [
            'DIRECT_DEBIT',
            'CREDIT_CARD',
            'INVOICE',
            'CASH_ON_DELIVERY',
            'PREPAYMENT',
            'CHEQUE',
            'PAYBOX',
            'PAYPAL',
            'CASH_ON_PICKUP',
            'FINANCING',
            'LEASING',
            'T_PAY',
            'GIROPAY',
            'GOOGLE_CHECKOUT',
            'SHOP_CARD',
            'DIRECT_E_BANKING',
            'OTHER'
        ];
        foreach ($node as $i => $method) {
            if (!\is_array($method)) {
                continue;
            }
            $method = $this->sanitizePaymentMethod($method);
            $i      = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            \preg_match(
                '/[\w.,!"§$%&\/()=`´+~*\';\-?{}\[\] ]+/u',
                $method['Name'],
                $hits1
            );
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['Name'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_NAME;
            }
            \preg_match('/[0-9]+/', $method['Sort'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['Sort'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SORT;
            }
            \preg_match('/[0-1]/', $method['SendMail'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['SendMail'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_MAIL;
            }
            \preg_match('/[A-Z_]+/', $method['TSCode'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) === \mb_strlen($method['TSCode'])) {
                if (!\in_array($method['TSCode'], $tsCodes, true)) {
                    return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
                }
            } else {
                return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
            }
            \preg_match('/[0-1]/', $method['PreOrder'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['PreOrder'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER;
            }
            \preg_match('/[0-1]/', $method['Soap'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['Soap'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOAP;
            }
            \preg_match('/[0-1]/', $method['Curl'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['Curl'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_CURL;
            }
            \preg_match('/[0-1]/', $method['Sockets'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['Sockets'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOCKETS;
            }
            \preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $method['ClassFile'], $hits1);
            if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($method['ClassFile'])) {
                if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['ClassFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_FILE;
                }
            } else {
                return InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE;
            }
            \preg_match('/[a-zA-Z0-9\/_\-]+/', $method['ClassName'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($method['ClassName'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME;
            }
            if (isset($method['TemplateFile']) && \mb_strlen($method['TemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['TemplateFile'],
                    $hits1
                );
                if (\mb_strlen($hits1[0]) !== \mb_strlen($method['TemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE;
                }
                if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['TemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE;
                }
            }
            if (isset($method['AdditionalTemplateFile']) && \mb_strlen($method['AdditionalTemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['AdditionalTemplateFile'],
                    $hits1
                );
                if (\mb_strlen($hits1[0]) !== \mb_strlen($method['AdditionalTemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE;
                }
                if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['AdditionalTemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE;
                }
            }
            if (empty($method['MethodLanguage']) || !\is_array($method['MethodLanguage'])) {
                return InstallCode::MISSING_PAYMENT_METHOD_LANGUAGES;
            }
            if (($res = $this->validateLocalization($method['MethodLanguage'])) !== InstallCode::OK) {
                return $res;
            }
            if (!isset($method['Setting']) || !\is_array($method['Setting']) || !\count($method['Setting']) === 0) {
                continue;
            }
            if (($res = $this->validateSettings($method['Setting'])) !== InstallCode::OK) {
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
            $l = (string)$l;
            \preg_match('/[0-9]+\sattr/', $l, $hits1);
            \preg_match('/[0-9]+/', $l, $hits2);
            if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                $len = \mb_strlen($localized['iso']);
                if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                    return InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO;
                }
            } elseif (isset($hits2[0]) && \mb_strlen($hits2[0]) === \mb_strlen($l)) {
                if (!isset($localized['Name'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                }
                \preg_match(
                    '/[\w.,!"§$%&\/()=`´+~*\';\-?{}\[\] ]+/u',
                    $localized['Name'],
                    $hits1
                );
                if (\mb_strlen($hits1[0]) !== \mb_strlen($localized['Name'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                }
                if (!isset($localized['ChargeName'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                }
                \preg_match(
                    '/[\w.,!"§$%&\/()=`´+~*\';\-?{}\[\] ]+/u',
                    $localized['ChargeName'],
                    $hits1
                );
                if (\mb_strlen($hits1[0]) !== \mb_strlen($localized['ChargeName'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                }
                if (!isset($localized['InfoText'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $settings
     * @return int
     */
    private function validateSettings(array $settings): int
    {
        $type = '';
        foreach ($settings as $j => $setting) {
            $j = (string)$j;
            \preg_match('/[0-9]+\sattr/', $j, $hits3);
            \preg_match('/[0-9]+/', $j, $hits4);
            if (isset($hits3[0]) && \mb_strlen($hits3[0]) === \mb_strlen($j)) {
                $type = $setting['type'];
                if (\mb_strlen($setting['type']) === 0) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE;
                }
                if (\mb_strlen($setting['sort']) === 0) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT;
                }
                if (\mb_strlen($setting['conf']) === 0) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF;
                }
            } elseif (isset($hits4[0]) && \mb_strlen($hits4[0]) === \mb_strlen($j)) {
                if (\mb_strlen($setting['Name']) === 0) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME;
                }
                if (\mb_strlen($setting['ValueName']) === 0) {
                    return InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME;
                }
                if ($type === InputType::SELECT) {
                    if (empty($setting['SelectboxOptions']) || !\is_array($setting['SelectboxOptions'])) {
                        return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                    }
                    if (\count($setting['SelectboxOptions'][0]) === 1) {
                        foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $options) {
                            $y = (string)$y;
                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                            \preg_match('/[0-9]+/', $y, $hits7);
                            if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                if (\mb_strlen($options['value']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                                if (\mb_strlen($options['sort']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                            } elseif (isset($hits7[0]) && \mb_strlen($hits7[0]) === \mb_strlen($y)) {
                                if (\mb_strlen($options) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                            }
                        }
                    } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                        if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                        if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                        if (\mb_strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                    }
                } elseif ($type === InputType::RADIO) {
                    if (empty($setting['RadioOptions']) || !\is_array($setting['RadioOptions'])) {
                        return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                    }
                    if (\count($setting['RadioOptions'][0]) === 1) {
                        foreach ($setting['RadioOptions'][0]['Option'] as $y => $options) {
                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                            \preg_match('/[0-9]+/', $y, $hits7);
                            if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                if (\mb_strlen($options['value']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                                if (\mb_strlen($options['sort']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                            } elseif (isset($hits7[0]) && \mb_strlen($hits7[0]) === \mb_strlen($y)) {
                                if (\mb_strlen($options) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                }
                            }
                        }
                    } elseif (\count($setting['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                        if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                        if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                        if (\mb_strlen($setting['RadioOptions'][0]['Option']) === 0) {
                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $method
     * @return array
     */
    private function sanitizePaymentMethod(array $method): array
    {
        $method['Name']      = $method['Name'] ?? '';
        $method['Sort']      = $method['Sort'] ?? '';
        $method['SendMail']  = $method['SendMail'] ?? '';
        $method['TSCode']    = $method['TSCode'] ?? '';
        $method['PreOrder']  = $method['PreOrder'] ?? '';
        $method['Soap']      = $method['Soap'] ?? '';
        $method['Curl']      = $method['Curl'] ?? '';
        $method['Sockets']   = $method['Sockets'] ?? '';
        $method['ClassName'] = $method['ClassName'] ?? '';
        $method['ClassFile'] = $method['ClassFile'] ?? '';

        return $method;
    }
}
