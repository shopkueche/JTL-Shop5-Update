<?php
/**
 * Add NL cron setting
 *
 * @author cr
 * @created Wed, 05 Jun 2019 08:17:05 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190605081705
 */
class Migration_20190605081705 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 'Add NL cron setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'newsletter_send_delay',
            '1',
            CONF_NEWSLETTER,
            'Newsletter Sendeverzögerung',
            'number',
            130,
            (object)[
                'cBeschreibung'     => 'Legt die Wartezeit (in Stunden) zwischen den Newsletter-Sendungen fest.',
                'nStandardAnzeigen' => 1
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('newsletter_send_delay');
    }
}
