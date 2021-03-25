<?php

/**
 * add lang vars for nova menu
 *
 * @author mh
 * @created Wed, 23 Oct 2019 14:20:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191023142000
 */
class Migration_20191023142000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add lang vars for nova menu';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'menuShow', '%s anzeigen');
        $this->setLocalization('eng', 'global', 'menuShow', 'Show %s');
        $this->setLocalization('ger', 'global', 'menuName', 'Menü');
        $this->setLocalization('eng', 'global', 'menuName', 'Menu');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('menuShow');
        $this->removeLocalization('menuName');
    }
}
