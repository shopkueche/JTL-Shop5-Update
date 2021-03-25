<?php declare(strict_types=1);
/**
 * remove_unused_configgroup_5_product_tagging_config
 *
 * @author je
 * @created Tue, 05 Jan 2021 09:37:53 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210105093753
 */
class Migration_20210105093753 extends Migration implements IMigration
{
    protected $author = 'je';
    protected $description = 'remove unused configgroup_5_product_tagging config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "DELETE FROM `teinstellungenconf` WHERE kEinstellungenSektion = 5 AND cWertName = 'configgroup_5_product_tagging'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO `teinstellungenconf`
                VALUES (626,5,'Produkttagging','','configgroup_5_product_tagging',NULL,'',1000,1,0,'N')"
        );
    }
}
