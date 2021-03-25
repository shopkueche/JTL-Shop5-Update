<?php
/**
 * Move language variables "invalidHash" und "invalidCustomer" to account data
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171215121900
 */
class Migration_20171215121900 extends Migration implements IMigration
{
    protected $author      = 'fg';
    protected $description = 'Move language variables "invalidHash" und "invalidCustomer" to account data';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->getDB()->update('tsprachwerte', 'cName', 'invalidHash', (object)['kSprachsektion' => 6]);
        $this->getDB()->update('tsprachwerte', 'cName', 'invalidCustomer', (object)['kSprachsektion' => 6]);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->getDB()->update('tsprachwerte', 'cName', 'invalidHash', (object)['kSprachsektion' => 4]);
        $this->getDB()->update('tsprachwerte', 'cName', 'invalidCustomer', (object)['kSprachsektion' => 4]);
    }
}
