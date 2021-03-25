<?php
/**
 * remove global html entity config
 *
 * @author fm
 * @created Mon, 11 Mar 2019 12:28:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190311122800
 */
class Migration_20190311122800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'remove global html entity config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('global_artikelname_htmlentities');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'global_artikelname_htmlentities',
            'N',
            CONF_GLOBAL,
            'HTML-Code Umwandlung bei Artikelnamen',
            'selectbox',
            280,
            (object)[
                'cBeschreibung' => 'Sollen Sonderzeichen im Artikelnamen in HTML Entities umgewandelt werden',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
        ]);
    }
}
