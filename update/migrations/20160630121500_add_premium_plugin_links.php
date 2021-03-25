<?php
/**
 * Adds backend links for premium plugins
 *
 * @author fm
 * @created Thu, 30 Jun 2016 12:15:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160630121500
 */
class Migration_20160630121500 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO `tadminmenu` (`kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`)
                VALUES ('18', 'core_jtl', 'Amazon Payments', 'premiumplugin.php?plugin_id=s360_amazon_lpa_shop4', 'PLUGIN_ADMIN_VIEW', '315')"
        );
        $this->execute(
            "INSERT INTO `tadminmenu` (`kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`)
              VALUES ('16', 'core_jtl', 'TrustedShops Trustbadge Reviews', 'premiumplugin.php?plugin_id=agws_ts_features', 'PLUGIN_ADMIN_VIEW', '315')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tadminmenu` WHERE `nSort`=315 AND cRecht='PLUGIN_ADMIN_VIEW'");
    }
}
