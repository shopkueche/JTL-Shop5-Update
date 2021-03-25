<?php
/**
 * add option for xselling show parent
 *
 * @author fp
 * @created Tue, 14 Jun 2016 15:25:25 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160614152525
 */
class Migration_20160614152525 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'artikeldetails_xselling_kauf_parent',
            'N',
            CONF_ARTIKELDETAILS,
            'Immer Vaterartikel anzeigen',
            'selectbox',
            230,
            (object)[
                'cBeschreibung' => 'Es werden immer die zugeh&ouml;rigen Vaterartikel angezeigt, auch wenn ' .
                    'tats&auml;chlich Kindartikel gekauft wurden.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('artikeldetails_xselling_kauf_parent');
    }
}
