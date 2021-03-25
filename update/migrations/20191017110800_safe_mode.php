<?php
/**
 * @author fm
 * @created Thu, 17 Oct 2019 11:08:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Media\Image;

/**
 * Class Migration_20191017110800
 */
class Migration_20191017110800 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add safe mode language vars';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'global',
            'safeModeActive',
            'Abgesicherter Modus aktiv. Gewisse Funktionalitäten stehen nicht zur Verfügung.'
        );
        $this->setLocalization(
            'eng',
            'global',
            'safeModeActive',
            'Safe mode enabled. Certain functionality will not be available.'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeLocalization('safeModeActive');
    }
}
