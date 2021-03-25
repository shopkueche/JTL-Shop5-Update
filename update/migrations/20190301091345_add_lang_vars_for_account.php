<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * add_lang_vars_for_account
 *
 * @author mh
 * @created Fri, 01 Mar 2019 09:13:45 +0100
 */

/**
 * Class Migration_20190301091345
 */
class Migration_20190301091345 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang vars for account';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'myOrders', 'Meine Bestellungen');
        $this->setLocalization('eng', 'global', 'myOrders', 'My orders');
        $this->setLocalization('ger', 'global', 'myPersonalData', 'Meine persönlichen Daten');
        $this->setLocalization('eng', 'global', 'myPersonalData', 'My personal data');
        $this->setLocalization('ger', 'global', 'myWishlists', 'Meine Wunschlisten');
        $this->setLocalization('eng', 'global', 'myWishlists', 'My wishlists');
        $this->setLocalization('ger', 'global', 'myCompareList', 'Meine Vergleichsliste');
        $this->setLocalization('eng', 'global', 'myCompareList', 'My comparelist');
        $this->setLocalization('ger', 'global', 'public', 'Öffentlich');
        $this->setLocalization('eng', 'global', 'public', 'public');
        $this->setLocalization('ger', 'global', 'private', 'Privat');
        $this->setLocalization('eng', 'global', 'private', 'private');
        $this->setLocalization('ger', 'global', 'and', 'und');
        $this->setLocalization('eng', 'global', 'and', 'and');
        $this->setLocalization('ger', 'global', 'currently', 'Aktuell');
        $this->setLocalization('eng', 'global', 'currently', 'Currently');
        $this->setLocalization('ger', 'global', 'myDownloads', 'Meine Downloads');
        $this->setLocalization('eng', 'global', 'myDownloads', 'My Downloads');
        $this->setLocalization('ger', 'global', 'miscellaneous', 'Sonstiges');
        $this->setLocalization('eng', 'global', 'miscellaneous', 'Miscellaneous');
        $this->setLocalization('ger', 'comparelist', 'goToCompareList', 'Zur Vergleichsliste');
        $this->setLocalization('eng', 'comparelist', 'goToCompareList', 'Go to comparelist');
        $this->setLocalization('ger', 'account data', 'noOrdersYet', 'Sie habe noch keine Bestellung abgegeben.');
        $this->setLocalization('eng', 'account data', 'noOrdersYet', 'There are no orders yet.');
        $this->setLocalization('ger', 'account data', 'noWishlist', 'Es ist keine Wunschliste vorhanden.');
        $this->setLocalization('eng', 'account data', 'noWishlist', 'There is no wishlist at the moment.');
        $this->setLocalization(
            'ger',
            'account data',
            'compareListItemCount',
            'Sie haben %d Artikel auf Ihrer Vergleichsliste.'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'compareListItemCount',
            'You have %d products on your comparelist.'
        );
        $this->setLocalization('ger', 'account data', 'editCustomerData', 'Kundendaten ändern');
        $this->setLocalization('eng', 'account data', 'editCustomerData', 'Save customer data');
        $this->setLocalization('ger', 'account data', 'orderOverview', 'Bestellübersicht');
        $this->setLocalization('eng', 'account data', 'orderOverview', 'Order overview');
        $this->setLocalization('ger', 'account data', 'subtotal', 'Zwischensumme');
        $this->setLocalization('eng', 'account data', 'subtotal', 'Subtotal');
        $this->setLocalization('ger', 'breadcrumb', 'bcOrder', 'Bestellung');
        $this->setLocalization('eng', 'breadcrumb', 'bcOrder', 'order');
        $this->setLocalization('ger', 'breadcrumb', 'bcWishlist', 'Wunschliste');
        $this->setLocalization('eng', 'breadcrumb', 'bcWishlist', 'Wishlist');

        $this->setLocalization(
            'ger',
            'login',
            'myAccountDesc',
            'Hier können Sie Ihre Adressdaten ändern und den Status Ihrer Bestellungen abfragen.'
        );
        $this->setLocalization(
            'eng',
            'login',
            'myAccountDesc',
            'Here you can change your address details and have a look at your order status.'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('myOrders');
        $this->removeLocalization('myPersonalData');
        $this->removeLocalization('myWishlists');
        $this->removeLocalization('myCompareList');
        $this->removeLocalization('goToCompareList');
        $this->removeLocalization('public');
        $this->removeLocalization('private');
        $this->removeLocalization('noOrdersYet');
        $this->removeLocalization('noWishlist');
        $this->removeLocalization('compareListItemCount');
        $this->removeLocalization('and');
        $this->removeLocalization('editCustomerData');
        $this->removeLocalization('orderOverview');
        $this->removeLocalization('subtotal');
        $this->removeLocalization('bcOrder');
        $this->removeLocalization('bcWishlist');
        $this->removeLocalization('myDownloads');
        $this->removeLocalization('miscellaneous');
        $this->removeLocalization('currently');

        $this->setLocalization(
            'ger',
            'login',
            'myAccountDesc',
            'Hier können Sie Ihre Adressdaten ändern, den Status Ihrer Bestellungen ' .
            'abfragen und Gutscheine weiterverschenken.'
        );
        $this->setLocalization(
            'eng',
            'login',
            'myAccountDesc',
            'You can change your address details here, can have a look at your ' .
            'order status and you can forward vouchers.'
        );
    }
}
