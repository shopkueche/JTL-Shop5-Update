<?php
/**
 * change wrong language-variable-values
 *
 * @author cr
 * @created Tue, 06 Mar 2018 16:09:13 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180306160913
 */
class Migration_20180306160913 extends Migration implements IMigration
{
    protected $author = 'cr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'uploadInvalidFormat', 'Die Datei entspricht nicht dem geforderten Format');
        $this->setLocalization('ger', 'global', 'paginationOrderUsefulness', 'Hilfreich');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'global', 'uploadInvalidFormat', 'Die Datei entspricht nicht dem geforderte Format');
        $this->setLocalization('ger', 'global', 'paginationOrderUsefulness', 'Hilreich');
    }
}
