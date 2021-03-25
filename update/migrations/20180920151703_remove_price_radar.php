<?php
/**
 * remove_price_radar
 *
 * @author mh
 * @created Thu, 20 Sep 2018 15:17:03 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180920151703
 */
class Migration_20180920151703 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove Priceradar';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "DELETE tboxvorlage, tboxen, tboxensichtbar, tboxsprache
                FROM tboxvorlage
                LEFT JOIN tboxen 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                LEFT JOIN tboxensichtbar
                    ON tboxensichtbar.kBox = tboxen.kBox
                LEFT JOIN tboxsprache
                    ON tboxsprache.kBox = tboxen.kBox
                WHERE tboxvorlage.cTemplate = 'box_priceradar.tpl'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO tboxvorlage 
                  (kBoxvorlage, kCustomID, eTyp, cName, cVerfuegbar, cTemplate) 
                VALUES (100, 0, 'tpl', 'Preisradar', '0', 'box_priceradar.tpl')"
        );
    }
}
