<?php declare(strict_types=1);

/**
 * @author ms
 * @created Thu, 14 May 2020 14:35:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200514143500
 */
class Migration_20200514143500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Adds lang var for privacy notice';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'privacyNotice', 'Bitte beachten Sie unsere Datenschutzerklärung');
        $this->setLocalization('eng', 'global', 'privacyNotice', 'Please see our privacy notice');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('privacyNotice', 'global');
    }
}
