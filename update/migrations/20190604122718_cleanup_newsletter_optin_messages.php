<?php
/**
 * cleanup newsletter optin messages
 *
 * @author cr
 * @created Tue, 04 Jun 2019 12:27:18 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190604122718
 */
class Migration_20190604122718 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Cleanup newsletter optin messages';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeLocalization('newsletterExists');
        $this->removeLocalization('newsletterDelete');

        $this->setLocalization('ger', 'messages', 'optinSucceededMailSent', 'Die Mail mit Ihrem Freischalt-Code wurde bereits an Sie verschickt');
        $this->setLocalization('eng', 'messages', 'optinSucceededMailSent', 'The mail with your activation-code was already sent.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('optinSucceededMailSent');

        $this->setLocalization('ger', 'errorMessages', 'newsletterDelete', 'Sie wurden erfolgreich aus unserem Newsletterverteiler ausgetragen.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterDelete', 'You have been successfully deleted from our News list.');
        $this->setLocalization('ger', 'errorMessages', 'newsletterExists', 'Fehler: Ihre E-Mail-Adresse ist bereits vorhanden.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterExists', 'Error: It appears that your E-Mail already exists.');
    }
}
