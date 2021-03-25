<?php
/**
 * add_filter_lang
 *
 * @author mh
 * @created Wed, 15 May 2019 15:09:02 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190515150902
 */
class Migration_20190515150902 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add filter lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'noFilterResults', 'Für die Filterung wurden keine Ergebnisse gefunden.');
        $this->setLocalization('eng', 'global', 'noFilterResults', 'No results found for this filter.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('noFilterResults');
    }
}
