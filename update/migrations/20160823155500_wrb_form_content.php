<?php
/**
 * WRB
 *
 * @author fm
 * @created Tue, 23 Aug 2016 15:55:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160823155500
 */
class Migration_20160823155500 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE ttext 
                ADD COLUMN cWRBFormContentHtml TEXT DEFAULT '',
                ADD COLUMN cWRBFormContentText TEXT DEFAULT ''"
        );
        $this->execute(
            'ALTER TABLE temailvorlage 
                ADD COLUMN nWRBForm TINYINT(3) UNSIGNED NOT NULL DEFAULT 0'
        );
        $this->execute(
            'ALTER TABLE tpluginemailvorlage 
                ADD COLUMN nWRBForm TINYINT(3) UNSIGNED NOT NULL DEFAULT 0'
        );
        $this->execute(
            'ALTER TABLE temailvorlageoriginal 
                ADD COLUMN nWRBForm TINYINT(3) UNSIGNED NOT NULL DEFAULT 0'
        );
        $this->setLocalization('ger', 'global', 'wrbform', 'Muster-Widerrufsbelehrungsformular');
        $this->setLocalization('eng', 'global', 'wrbform', 'Model withdrawal form');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('ttext', 'cWRBFormContentHtml');
        $this->dropColumn('ttext', 'cWRBFormContentText');
        $this->dropColumn('temailvorlage', 'nWRBForm');
        $this->dropColumn('tpluginemailvorlage', 'nWRBForm');
        $this->dropColumn('temailvorlageoriginal', 'nWRBForm');
        $this->execute("DELETE FROM tsprachwerte WHERE cName = 'wrbform' AND kSprachsektion = 1");
    }
}
