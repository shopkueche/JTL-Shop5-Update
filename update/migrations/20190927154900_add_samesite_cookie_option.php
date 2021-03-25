<?php declare(strict_types=1);
/**
 * @author fm
 * @created Fri, 27 Sep 2019 15:49:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190927154900
 */
class Migration_20190927154900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add samesite cookie option';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'global_cookie_samesite',
            'S',
            CONF_GLOBAL,
            'Samesite',
            'selectbox',
            1516,
            (object)[
                'cBeschreibung' => 'Samesite-Header für Cookies',
                'inputOptions'  => [
                    'S'      => 'Standard',
                    'N'      => 'Deaktiviert',
                    'Lax'    => 'Lax',
                    'Strict' => 'Strict',
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('global_cookie_samesite');
    }
}
