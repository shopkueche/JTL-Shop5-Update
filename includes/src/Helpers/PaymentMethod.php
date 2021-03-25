<?php

namespace JTL\Helpers;

use JTL\Checkout\Zahlungsart;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class PaymentMethod
 * @package JTL\Helpers
 */
class PaymentMethod
{
    /**
     * @param \PaymentMethod|Zahlungsart $paymentMethod
     * @return bool
     */
    public static function shippingMethodWithValidPaymentMethod($paymentMethod): bool
    {
        if (!isset($paymentMethod->cModulId)) {
            return false;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $conf                         = Shop::getSettings([\CONF_ZAHLUNGSARTEN])['zahlungsarten'];
        $paymentMethod->einstellungen = $conf;
        switch ($paymentMethod->cModulId) {
            case 'za_ueberweisung_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_ueberweisung_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_ueberweisung_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_ueberweisung_max'])) {
                    return false;
                }
                break;
            case 'za_nachnahme_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_nachnahme_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_nachnahme_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_nachnahme_max'])) {
                    return false;
                }
                break;
            case 'za_kreditkarte_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_kreditkarte_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_kreditkarte_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_kreditkarte_max'])) {
                    return false;
                }
                break;
            case 'za_rechnung_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_rechnung_min_bestellungen'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_rechnung_min'])) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_rechnung_max'])) {
                    return false;
                }
                break;
            case 'za_lastschrift_jtl':
                if (!\pruefeZahlungsartMinBestellungen($conf['zahlungsart_lastschrift_min_bestellungen'])) {
                    return false;
                }

                if (!\pruefeZahlungsartMinBestellwert($conf['zahlungsart_lastschrift_min'])) {
                    return false;
                }

                if (!\pruefeZahlungsartMaxBestellwert($conf['zahlungsart_lastschrift_max'])) {
                    return false;
                }
                break;
            case 'za_barzahlung_jtl':
                if (!\pruefeZahlungsartMinBestellungen(!empty($conf['zahlungsart_barzahlung_min_bestellungen'])
                    ? $conf['zahlungsart_barzahlung_min_bestellungen']
                    : 0)
                ) {
                    return false;
                }
                if (!\pruefeZahlungsartMinBestellwert(!empty($conf['zahlungsart_barzahlung_min'])
                    ? $conf['zahlungsart_barzahlung_min']
                    : 0)
                ) {
                    return false;
                }
                if (!\pruefeZahlungsartMaxBestellwert(!empty($conf['zahlungsart_barzahlung_max'])
                    ? $conf['zahlungsart_barzahlung_max']
                    : 0)
                ) {
                    return false;
                }
                break;
            case 'za_null_jtl':
                break;
            default:
                $payMethod = LegacyMethod::create($paymentMethod->cModulId);
                if ($payMethod !== null) {
                    return $payMethod->isValidIntern([Frontend::getCustomer(), Frontend::getCart()]);
                }
                break;
        }

        return true;
    }

    /**
     * @former pruefeZahlungsartNutzbarkeit()
     */
    public static function checkPaymentMethodAvailability(): void
    {
        foreach (Shop::Container()->getDB()->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            'kZahlungsart, nSOAP, nCURL, nSOCKETS, nNutzbar'
        ) as $paymentMethod) {
            self::activatePaymentMethod($paymentMethod);
        }
    }

    /**
     * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
     *
     * @param Zahlungsart|PaymentMethod|object $paymentMethod
     * @return bool
     * @former aktiviereZahlungsart()
     */
    public static function activatePaymentMethod($paymentMethod): bool
    {
        if ($paymentMethod->kZahlungsart > 0) {
            $paymentID = (int)$paymentMethod->kZahlungsart;

            if (empty($paymentMethod->nSOAP) && empty($paymentMethod->nCURL) && empty($paymentMethod->nSOCKETS)) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nSOAP) && PHPSettings::checkSOAP()) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nCURL) && PHPSettings::checkCURL()) {
                $isUsable = 1;
            } elseif (!empty($paymentMethod->nSOCKETS) && PHPSettings::checkSockets()) {
                $isUsable = 1;
            } else {
                $isUsable = 0;
            }

            if (!isset($paymentMethod->nNutzbar) || (int)$paymentMethod->nNutzbar !== $isUsable) {
                Shop::Container()->getDB()->update(
                    'tzahlungsart',
                    'kZahlungsart',
                    $paymentID,
                    (object)['nNutzbar' => $isUsable]
                );
            }

            return $isUsable > 0;
        }

        return false;
    }
}
