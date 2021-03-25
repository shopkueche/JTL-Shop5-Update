<?php
/**
 * adds aria language section and variables
 *
 * @author ms
 * @created Thu, 15 Nov 2018 11:55:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181115115500
 */
class Migration_20181115115500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds aria language section and variables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('INSERT INTO tsprachsektion (cName) VALUES ("aria");');

        $this->setLocalization('ger', 'aria', 'primary', 'Kontext: hauptsächlich');
        $this->setLocalization('eng', 'aria', 'primary', 'primary context');

        $this->setLocalization('ger', 'aria', 'secondary', 'Kontext: nebensächlich');
        $this->setLocalization('eng', 'aria', 'secondary', 'secondary context');

        $this->setLocalization('ger', 'aria', 'success', 'Kontext: Erfolg');
        $this->setLocalization('eng', 'aria', 'success', 'success context');

        $this->setLocalization('ger', 'aria', 'danger', 'Kontext: Achtung');
        $this->setLocalization('eng', 'aria', 'danger', 'danger context');

        $this->setLocalization('ger', 'aria', 'warning', 'Kontext: Warnung');
        $this->setLocalization('eng', 'aria', 'warning', 'warning context');

        $this->setLocalization('ger', 'aria', 'info', 'Kontext: Information');
        $this->setLocalization('eng', 'aria', 'info', 'information context');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('primary');
        $this->removeLocalization('secondary');
        $this->removeLocalization('success');
        $this->removeLocalization('danger');
        $this->removeLocalization('warning');
        $this->removeLocalization('info');
        $this->execute('DELETE FROM tsprachsektion WHERE cName = "aria";');
    }
}
