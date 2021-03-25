<?php

namespace JTL\Backend;

use Exception;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class TwoFAEmergency
 * @package Backend
 */
class TwoFAEmergency
{
    /**
     * all the generated emergency-codes, in plain-text
     *
     * @var array
     */
    private $codes = [];

    /**
     * generate 10 codes (maybe should placed into a config)
     *
     * @var int
     */
    private $codeCount = 10;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * TwoFAEmergency constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * create a pool of emergency-codes
     * for the current admin-account and store them in the DB.
     *
     * @param object $userTuple - user-data, as delivered from TwoFA-object
     * @return array - new created emergency-codes (as written into the DB)
     * @throws Exception
     */
    public function createNewCodes($userTuple): array
    {
        $passwordService = Shop::Container()->getPasswordService();
        $bindings        = [];
        $rowValues       = '';
        $valCount        = 'a';
        for ($i = 0; $i < $this->codeCount; $i++) {
            $code          = \mb_substr(\md5(\rand(1000, 9000)), 0, 16);
            $this->codes[] = $code;

            if ($rowValues !== '') {
                $rowValues .= ', ';
            }
            $code = $passwordService->hash($code);

            // to prevent the fireing from within a loop against the DB
            // we build a values-string (like this: "(:a, :b), (:c, :d), ... " )
            // and an according array
            $bindings[$valCount] = $userTuple->kAdminlogin;
            $rowValues          .= '(:' . $valCount . ',';
            $valCount++;
            $bindings[$valCount] = $code;
            $rowValues          .= ' :' . $valCount . ')';
            $valCount++;
        }
        // now write into the DB what we got till now
        $this->db->queryPrepared(
            'INSERT INTO `tadmin2facodes`(`kAdminlogin`, `cEmergencyCode`) VALUES' . $rowValues,
            $bindings,
            ReturnType::AFFECTED_ROWS
        );

        return $this->codes;
    }

    /**
     * delete all the existing codes for the given user
     *
     * @param object $userTuple - user-data, as delivered from TwoFA-object
     */
    public function removeExistingCodes($userTuple): void
    {
        $effected = $this->db->deleteRow(
            'tadmin2facodes',
            'kAdminlogin',
            $userTuple->kAdminlogin
        );
        if ($this->codeCount !== $effected) {
            Shop::Container()->getLogService()->error(
                '2FA-Notfall-Codes für diesen Account konnten nicht entfernt werden.'
            );
        }
    }

    /**
     * check a given code for his existence in a given users emergency-code pool
     * (keep this method as fast as possible, because it's called during each admin-login)
     *
     * @param int    $adminID - admin-account ID
     * @param string $code - code, as typed in the login-fields
     * @return bool - true="valid emergency-code", false="not a valid emergency-code"
     */
    public function isValidEmergencyCode($adminID, $code): bool
    {
        $hashes = $this->db->selectArray('tadmin2facodes', 'kAdminlogin', $adminID);
        if (1 > \count($hashes)) {
            return false; // no emergency-codes are there
        }

        foreach ($hashes as $item) {
            if (\password_verify($code, $item->cEmergencyCode) === true) {
                // valid code found. remove it from DB and return a 'true'
                $effected = $this->db->delete(
                    'tadmin2facodes',
                    ['kAdminlogin', 'cEmergencyCode'],
                    [$adminID, $item->cEmergencyCode]
                );
                if ($effected !== 1) {
                    Shop::Container()->getLogService()->error('2FA-Notfall-Code konnte nicht gelöscht werden.');
                }

                return true;
            }
        }

        return false; // not a valid emergency code, so no further action here
    }
}
