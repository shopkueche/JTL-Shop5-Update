<?php
/**
 * add_language_variable_descriptionview
 *
 * @author msc
 * @created Fri, 12 Aug 2016 11:42:10 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160812114210
 */
class Migration_20160812114210 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'showDescription', 'Beschreibung anzeigen');
        $this->setLocalization('eng', 'global', 'showDescription', 'Show description');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('showDescription');
    }
}
