<?php
/**
 * add_lang_invalid_url
 *
 * @author mh
 * @created Tue, 28 Aug 2018 13:05:42 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180828130542
 */
class Migration_20180828130542 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang variable invalidURL';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'invalidURL', 'Bitte geben Sie eine valide URL ein.');
        $this->setLocalization('eng', 'global', 'invalidURL', 'Please enter a valid url.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('invalidURL');
    }
}
