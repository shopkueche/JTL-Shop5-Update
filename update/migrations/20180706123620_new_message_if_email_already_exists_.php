<?php
/**
 * New Message if email already exists.
 *
 * @author fp
 * @created Fri, 06 Jul 2018 12:36:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180706123620
 */
class Migration_20180706123620 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'New Message if email already exists.';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'account data', 'emailAlreadyExists', 'Zu der von Ihnen eingegeben E-Mail-Adresse existiert bereits ein Kundenkonto in unserem Shop. Wenn Sie die Bestellung mit Ihrem vorhandenem Kundenkonto abschließen möchten, melden Sie sich bitte mit Ihrer E-Mail-Adresse und Ihrem Passwort an. Wenn Sie als Gast fortfahren möchten, deaktivieren Sie die Option "Neues Kundenkonto erstellen".');
        $this->setLocalization('eng', 'account data', 'emailAlreadyExists', 'A customer account already exists in our shop for the email address you have entered. If you want to complete the order with your existing customer account, please sign in with your email address and password. If you wish to proceed as a guest, please deactivate the option "Create new customer account".');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'account data', 'emailAlreadyExists', 'Diese E-Mail-Adresse ist bereits vergeben. Bitte geben Sie eine andere  ein oder melden Sie sich mit dieser E-Mail-Adresse an. ');
        $this->setLocalization('eng', 'account data', 'emailAlreadyExists', 'This E-Mail-Address is already in use. Please select a different one or log in with your E-Mail-Address.');
    }
}
