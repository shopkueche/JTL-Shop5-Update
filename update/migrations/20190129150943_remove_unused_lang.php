<?php
/**
 * remove_unused_lang
 *
 * @author mh
 * @created Tue, 29 Jan 2019 15:09:43 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190129150943
 */
class Migration_20190129150943 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'remove unused lang variables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeLocalization('goToContactForm');
        $this->removeLocalization('payWithUosCc');
        $this->removeLocalization('payWithUosDd');
        $this->removeLocalization('acceptAgb');
        $this->removeLocalization('available');
        $this->execute('DELETE FROM tsprachwerte WHERE cName="next" AND `kSprachsektion`=12');
        $this->execute('DELETE FROM tsprachwerte WHERE cName="previous" AND `kSprachsektion`=12');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('eng', 'global', 'goToContactForm', 'Contact us');
        $this->setLocalization('eng', 'global', 'payWithUosCc', 'Pay now with credit card (via United Online Services)');
        $this->setLocalization('eng', 'global', 'payWithUosDd', 'Pay now with direct debit (via United Online Services)');
        $this->setLocalization('eng', 'product rating', 'next', 'Next');
        $this->setLocalization('eng', 'product rating', 'previous', 'Previous');

        $this->setLocalization('eng', 'checkout', 'acceptAgb', 'Please accept our terms and conditions!');
        $this->setLocalization('ger', 'checkout', 'acceptAgb', 'Zum Fortfahren müssen Sie unsere AGB akzeptieren.');

        $this->setLocalization('eng', 'global', 'available', 'Available');
        $this->setLocalization('ger', 'global', 'available', 'Verfügbar ab');
    }
}
