<?php
/**
 * Add lang var note
 *
 * @author mh
 * @created Wed, 12 Aug 2020 14:53:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200812145300
 *
 */
class Migration_20200812145300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang var note';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'yourNote', 'Ihre Notiz');
        $this->setLocalization('eng', 'global', 'yourNote', 'Your note');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('yourNote');
    }
}
