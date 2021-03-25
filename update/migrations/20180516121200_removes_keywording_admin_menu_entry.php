<?php
/**
 * removed keywording admin menu entry
 *
 * @author fm
 * @created Wed, 16 May 2018 13:13:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180516121200
 */
class Migration_20180516121200 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'keywording.php'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("INSERT INTO `tadminmenu` 
            (`kAdminmenu`, `kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) 
            VALUES (8,7,'core_jtl','Meta-Keywords Blacklist','keywording.php','SETTINGS_META_KEYWORD_BLACKLIST_VIEW', 20)");
    }
}
