<?php

namespace JTL\VerificationVAT;

/**
 * Class VATCheckNonEU
 * @package JTL\VerificationVAT
 */
class VATCheckNonEU extends AbstractVATCheck
{
    /**
     * parse the non-EU string by convention
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
        $VatParser = new VATCheckVatParserNonEU($this->condenseSpaces($ustID));
        if ($VatParser->parseVatId() === true) {
            return [
                'success'   => true,
                'errortype' => 'parse',
                'errorcode' => '',
            ];
        }

        return [
            'success'   => false,
            'errortype' => 'parse',
            'errorcode' => VATCheckInterface::ERR_PATTERN_MISMATCH,
            'errorinfo' => ($szErrorInfo = $VatParser->getErrorInfo()) !== '' ? $szErrorInfo : ''
        ];
    }
}
