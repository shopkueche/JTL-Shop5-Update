<?php
/**
 * overlays_template_specific
 *
 * @author mh
 * @created Tue, 11 Dec 2018 12:08:13 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181211120813
 */
class Migration_20181211120813 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'make overlays template specific';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tsuchspecialoverlaysprache`
                          ADD COLUMN `cTemplate` VARCHAR(255) NOT NULL AFTER `kSprache`,
                          DROP PRIMARY KEY,
                          ADD PRIMARY KEY (`kSuchspecialOverlay`, `kSprache`, `cTemplate`)');
        $this->execute("UPDATE `tsuchspecialoverlaysprache` SET `cTemplate` = 'default'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tsuchspecialoverlaysprache` WHERE `cTemplate` != 'default'");
        $this->execute('ALTER TABLE `tsuchspecialoverlaysprache`
                           DROP COLUMN `cTemplate`,
                           DROP PRIMARY KEY,
                           ADD PRIMARY KEY (`kSuchspecialOverlay`, `kSprache`)');
    }
}
