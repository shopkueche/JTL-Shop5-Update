<?php

namespace JTL\VerificationVAT;

use JTL\Shop;

/**
 * Class VATCheck
 * @package JTL\VerificationVAT
 */
class VATCheck
{
    /**
     * @var VATCheckInterface
     */
    private $location;

    /**
     * @var string
     */
    private $ustID;

    /**
     * VATCheck constructor.
     * @param string $ustID
     */
    public function __construct(string $ustID = '')
    {
        $slots       = new VATCheckDownSlots();
        $logger      = Shop::Container()->getLogService();
        $this->ustID = $ustID;
        if ($this->startsWith($this->ustID, 'CHE')) {
            $this->location = new VATCheckNonEU($slots, $logger);
        } else {
            $this->location = new VATCheckEU($slots, $logger);
        }
    }

    /**
     * check the UstID
     *
     * return a array of check-results
     * [
     *        success   : boolean, "true" = all checks were fine, "false" somthing went wrong
     *      , errortype : string, which type of error was occure, time- or parse-error
     *      , errorcode : string, numerical code to identify the error
     *      , errorinfo : additional information to show it the user in the frontend
     * ]
     *
     * @return mixed  array if error, location-object otherwise
     */
    public function doCheckID()
    {
        if ($this->ustID === '') {
            return [
                'success'   => false,
                'errortype' => 'parse',
                'errorcode' => VATCheckInterface::ERR_NO_ID_GIVEN,  // error: no $ustID was given
                'errorinfo' => ''
            ];
        }

        return $this->location->doCheckID($this->ustID);
    }

    /**
     * @param string $sourceString
     * @param string $pattern
     * @return bool
     */
    public function startsWith(string $sourceString = '', string $pattern = '') : bool
    {
        if ($sourceString === '') {
            return false;
        }
        if ($pattern === '') {
            return true;
        }

        return \strpos($sourceString, $pattern) === 0;
    }
}
