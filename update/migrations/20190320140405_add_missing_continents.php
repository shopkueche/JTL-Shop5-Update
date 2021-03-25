<?php
/**
 *
 *
 * @author mh
 * @created Wed, 20 Mar 2019 14:04:05 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190320140405 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing continents';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE tland
                SET cKontinent = 'Nordamerika'
                WHERE cISO
                  IN ('AG', 'AI', 'AW', 'BB', 'BS', 'CU', 'DM', 'DO', 'GD', 'GP', 'HT', 'KN', 'KY', 'LC', 'MQ', 'MS', 'PR', 'TC', 'TT', 'VC', 'JM')"
        );
        $this->execute(
            "UPDATE tland
                SET cKontinent = 'Antarktis'
                WHERE cISO
                  IN ('BV', 'HM')"
        );
        $this->execute(
            "UPDATE tland
                SET cKontinent = 'Asien'
                WHERE cISO
                  IN ('CC', 'CX', 'MO', 'UZ', 'KZ')"
        );
        $this->execute(
            "UPDATE tland
                SET cKontinent = 'Afrika'
                WHERE cISO
                  IN ('RE', 'SH', 'YT', 'EG')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {

    }
}
