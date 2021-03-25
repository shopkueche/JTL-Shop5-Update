<?php
/**
 * Add option to switch sitemap ping to Google and Bing on or off
 *
 * @author dr
 * @created Wed, 21 Sep 2016 10:32:17 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160921103217
 */
class Migration_20160921103217 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add option to switch sitemap ping to Google and Bing on or off';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'sitemap_google_ping',
            'N',
            CONF_SITEMAP,
            'Sitemap an Google und Bing &uuml;bermitteln nach Export',
            'selectbox',
            180,
            (object)[
                'cBeschreibung' => 'Soll nach dem erfolgreichen Export der sitemap.xml und der sitemap_index.xml ein ' .
                    'Ping an Google und Bing durchgef&uuml;hrt werden, so dass die Website schnellstm&ouml;glich ' .
                    'gecrawlt wird?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('sitemap_google_ping');
    }
}
