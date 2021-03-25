<?php
/**
 * Add 'google two-factor-authentication'
 * Issue #276
 *
 * @author root
 * @created Wed, 27 Jul 2016 14:14:07 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160727141407
 */
class Migration_20160727141407 extends Migration implements IMigration
{
    protected $author = 'cr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE tadminlogin ADD b2FAauth tinyint(1) default 0, ADD c2FAauthSecret varchar(100) default '';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tadminlogin', 'b2FAauth');
        $this->dropColumn('tadminlogin', 'c2FAauthSecret');
    }
}
