<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200820125600
 */
class Migration_20200820125600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add direct advertising setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'direct_advertising',
            'N',
            CONF_KUNDEN,
            'Hinweis auf Direktwerbung anzeigen',
            'selectbox',
            240,
            (object)[
                'cBeschreibung' => 'Der Hinweis wird bei der Registrierung unter der E-Mail angezeigt.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );

        $this->setLocalization('ger', 'checkout', 'directAdvertising', 'Soweit Sie nicht widersprochen haben, nutzen wir Ihre E-Mail-Adresse, die wir im Rahmen des Verkaufes einer Ware oder Dienstleistung erhalten haben, für die elektronische Übersendung von Werbung für eigene Waren oder Dienstleistungen, die denen ähnlich sind, die Sie bereits bei uns erworbenen haben. Sie können dieser Verwendung Ihrer E-Mail-Adresse jederzeit durch eine Mitteilung an uns widersprechen. Die Kontaktdaten für die Ausübung des Widerspruchs finden Sie im Impressum. Sie können auch den dafür vorgesehenen Link in der Werbemail nutzen. Hierfür entstehen keine anderen als die Übermittlungskosten nach den Basistarifen.');
        $this->setLocalization('eng', 'checkout', 'directAdvertising', 'If you have not objected to this, we will use the email address that we have acquired from you during a former sale of a product or service to email you advertisements for our own products and services that are similar to those you have already purchased from us. You can object to this use of your email address at any time by contacting us. Please find the contact data for the withdrawal in the legal notice. You can also use the link that is intended for this purpose in the advertisement email itself. For this, no further costs arise than the transmission costs according to the base tariffs.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('direct_advertising');

        $this->removeLocalization('directAdvertising', 'checkout');
    }
}
