<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Remove marketplace admin menu entry and widget
 *
 * @author dr
 */

class Migration_20180731094600 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Remove marketplace admin menu entry and widget';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'marktplatz.php'");
        $this->execute("DELETE FROM tadminwidgets WHERE cClass = 'Marketplace'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO tadminmenu (kAdminmenu, kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
                VALUES (81, 5, 'core_jtl', 'Marktplatz', 'marktplatz.php', 'PLUGIN_ADMIN_VIEW', 80)"
        );

        $this->execute(
            "INSERT INTO tadminwidgets (
                    kWidget, kPlugin, cTitle, cClass, eContainer, cDescription, nPos, bExpanded, bActive
                )
                VALUES (101, 0, 'Marktplatz', 'Marketplace', 'center', 'JTL-Marktplatz (Erweiterungen)', 3, 1, 1)"
        );
    }
}
