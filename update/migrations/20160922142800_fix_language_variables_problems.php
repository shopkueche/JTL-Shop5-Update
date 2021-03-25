<?php
/**
 * fix some language variable problems
 *
 * @author msc
 * @created Thu, 22 Sep 2016 14:28:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160922142800
 */
class Migration_20160922142800 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'noShippingMethodsAvailable', 'Es steht keine Versandart für Ihre Bestellung zur Verfügung. Bitte kontaktieren Sie uns direkt, um diese Bestellung abzuwickeln.');
        $this->setLocalization('ger', 'messages', 'wishlistDelAll', 'Alle Artikel auf Ihrem Wunschzettel wurden gelöscht.');
        $this->setLocalization('ger', 'errorMessages', 'newsletterNoactive', 'Fehler: Ihr Freischaltcode wurde nicht gefunden.');
        $this->setLocalization('ger', 'global', 'incorrectEmailPlz', 'Es existiert kein Kunde mit angegebener E-Mail-Adresse und PLZ. Bitte versuchen Sie es noch einmal.');
        $this->setLocalization('ger', 'global', 'incorrectEmail', 'Es existiert kein Kunde mit der angegebenen E-Mail-Adresse. Bitte versuchen Sie es noch einmal.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'noShippingMethodsAvailable', 'Es steht keine Versandart für Ihre Bestellung zur Verfügung. Bitte kontakieren Sie uns direkt, um diese Bestellung abzuwickeln.');
        $this->setLocalization('ger', 'messages', 'wishlistDelAll', 'Alle Artikel auf Ihrer Wunschzettel wurden gelöscht.');
        $this->setLocalization('ger', 'errorMessages', 'newsletterNoactive', 'Fehler: Ihre Freischaltcode wurde nicht gefunden.');
        $this->setLocalization('ger', 'global', 'incorrectEmailPlz', 'Es existiert kein Kunde mit angegebener E-Mail-Adresse und PLZ. Bitte versuchen Sie es nocheinmal.');
        $this->setLocalization('ger', 'global', 'incorrectEmail', 'Es existiert kein Kunde mit der angegebenen E-Mail-Adresse. Bitte versuchen Sie es nocheinmal.');
    }
}
