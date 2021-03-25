<?php
/**
 * @author fm
 * @created Wed, 03 Apr 2019 17:49:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190403174900
 */
class Migration_20190403174900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'removed old exports';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "DELETE FROM texportformat 
                WHERE dZuletztErstellt IS NULL 
                AND kPlugin = 0
                AND cName IN ('Hardwareschotte', 'Kelkoo', 'Become Europe (become.eu)',
                              'Billiger', 'Geizhals', 'Preisauskunft',
                              'Preistrend', 'Shopboy', 'Idealo', 'Preisroboter', 'Milando', 'Channelpilot',
                             'Preissuchmaschine', 'Elm@r Produktdatei', 'Yatego Neu', 'LeGuide.com', 'Twenga'
                             )");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
