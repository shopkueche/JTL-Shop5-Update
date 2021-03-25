<?php

namespace JTL\VerificationVAT;

use JTL\Shop;
use SoapClient;

/**
 * Class VATCheckEU
 * @package JTL\VerificationVAT
 * External documentation
 * @link http://ec.europa.eu/taxation_customs/vies/faq.html
 * European Commission
 * VIES (VAT Information Exchange System)
 * @link https://ec.europa.eu/taxation_customs/business/vat/eu-vat-rules-topic/vies-vat-information-exchange-system-enquiries_en
 */
class VATCheckEU extends AbstractVATCheck
{
    /**
     * @var string
     */
    private $viesWSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * At this moment, the VIES-system, does not return any information other than "valid" or "invalid"
     * by giving a boolean value back via SOAP.
     * So we keep this error-string only for a possible future usage - currently they are not used.
     *
     * @var array
     */
    private $miasAnswerStrings = [
        0  => 'MwSt-Nummer gültig.',
        10 => 'MwSt-Nummer ungültig.', // (D.h. die eingegebene Nummer ist zumindest an dem angegebenen Tag ungültig)
        20 => 'Bearbeitung derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.',
        // (D.h. es gibt ein Problem mit dem Netz oder mit der Web-Anwendung)
        30 => 'Bearbeitung im Mitgliedstaat derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später.',
        // (D.h. die Anwendung ist in dem Mitgliedstaat, der die von Ihnen eingegebene MwSt-Nummer erteilt hat,
        // derzeit nicht möglich)
        40 => 'Unvollständige oder fehlerhafte Dateneingabe', // (MwSt-Nummer + Mitgliedstaat)
        50 => 'Zeitüberschreitung. Bitte wiederholen Sie Ihre Anfrage später.'
    ];

    /**
     * ask the remote APIs of the VIES-online-system
     *
     * return a array of check-results
     * [
     *        success   : boolean, "true" = all checks were fine, "false" somthing went wrong
     *      , errortype : string, which type of error was occure, time- or parse-error
     *      , errorcode : string, numerical code to identify the error
     *      , errorinfo : additional information to show it the user in the frontend
     * ]
     *
     * @param string $ustID
     * @return array
     */
    public function doCheckID(string $ustID): array
    {
        if (!\extension_loaded('soap')) {
            return [
                'success'   => false,
                'errortype' => 'core',
                'errorcode' => -1,
                'errorinfo' => 'VAT check not possible! Module "php_soap" was disabled.'
            ];
        }

        $vatParser = new VATCheckVatParser($this->condenseSpaces($ustID));
        if ($vatParser->parseVatId() === true) {
            [$countryCode, $vatNumber] = $vatParser->getIdAsParams();
        } else {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => $vatParser->getErrorCode(),
                'errorinfo' => ($errorInfo = $vatParser->getErrorInfo()) !== '' ? $errorInfo : ''
            ];
        }
        // asking the remote service if the VAT-office is reachable
        if ($this->downTimes->isDown($countryCode) === false) {
            $soap   = new SoapClient($this->viesWSDL);
            $result = null;
            try {
                $result = $soap->checkVat(['countryCode' => $countryCode, 'vatNumber' => $vatNumber]);
            } catch (\Exception $e) {
                Shop::Container()->getLogService()->warn('VAT ID problem: ' . $e->getMessage());
            }

            if ($result !== null && $result->valid === true) {
                $this->logger->notice('VAT ID valid. (' . \print_r($result, true) . ')');

                return [
                    'success'   => true,
                    'errortype' => 'vies',
                    'errorcode' => ''
                ];
            }
            $this->logger->notice('VAT ID invalid! (' . \print_r($result, true) . ')');

            return [
                'success'   => false,
                'errortype' => 'vies',
                'errorcode' => 5 // error: ID is invalid according to the VIES-system
            ];
        }
        // inform the user: "The VAT-office in this country has closed this time."
        $this->logger->notice('TAX authority of this country currently not available. (ID: ' . $ustID . ')');

        return [
            'success'   => false,
            'errortype' => 'time',
            'errorcode' => 200,
            'errorinfo' => $this->downTimes->getDownInfo() // the time, till which the office has closed
        ];
    }
}
