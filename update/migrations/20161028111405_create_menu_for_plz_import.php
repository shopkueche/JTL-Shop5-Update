<?php
/**
 * Create Menu for PLZ import
 *
 * @author fp
 * @created Fri, 28 Oct 2016 11:14:05 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20161028111405
 */
class Migration_20161028111405 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @param int $kAdminmenueGruppe
     */
    protected function reorderMenu(int $kAdminmenueGruppe): void
    {
        $this->execute('SET @SortStart = 0');
        $this->execute(
            'UPDATE tadminmenu SET nSort = @SortStart:=@SortStart + 10
                WHERE kAdminmenueGruppe = ' . $kAdminmenueGruppe . ' 
                ORDER BY nSort;'
        );
    }

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->reorderMenu(11);
        $this->execute(
            "INSERT INTO tadminrecht (cRecht, cBeschreibung, kAdminrechtemodul) 
                SELECT 'PLZ_ORT_IMPORT_VIEW', 'PLZ-Import', kAdminrechtemodul 
                FROM tadminrechtemodul 
                WHERE cName = 'Import / Export'"
        );
        $this->execute(
            "INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort) 
                SELECT kAdminmenueGruppe, cModulId, 'PLZ-Import', 'plz_ort_import.php', 'PLZ_ORT_IMPORT_VIEW', 55 
                FROM tadminmenugruppe 
                WHERE cName = 'Wartung'"
        );
        $this->reorderMenu(11);

        MigrationHelper::createIndex('tplz', ['cLandISO', 'cPLZ', 'cOrt'], 'PLZ_ORT_UNIQUE');
        $this->execute(
            'CREATE TABLE tplz_backup LIKE tplz'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'DROP TABLE IF EXISTS tplz_backup'
        );

        MigrationHelper::createIndex('tplz', ['cPLZ', 'cOrt'], 'PLZ_ORT_UNIQUE');

        $this->execute(
            "DELETE FROM tadminmenu WHERE cURL = 'plz_ort_import.php'"
        );
        $this->execute(
            "DELETE FROM tadminrecht WHERE cRecht = 'PLZ_ORT_IMPORT_VIEW'"
        );
        $this->reorderMenu(11);
    }
}
