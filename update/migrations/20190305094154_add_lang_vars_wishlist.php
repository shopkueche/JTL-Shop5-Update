<?php
/**
 * add_lang_vars_wishlist
 *
 * @author mh
 * @created Tue, 05 Mar 2019 09:41:54 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190305094154
 */
class Migration_20190305094154 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang vars wishlist';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('INSERT INTO tsprachsektion (cName) VALUES ("wishlist");');

        $this->setLocalization('ger', 'global', 'activate', 'Aktivieren');
        $this->setLocalization('eng', 'global', 'activate', 'Activate');
        $this->setLocalization('ger', 'global', 'rename', 'Umbenennen');
        $this->setLocalization('eng', 'global', 'rename', 'Rename');
        $this->setLocalization('ger', 'global', 'copied', 'kopiert');
        $this->setLocalization('eng', 'global', 'copied', 'copied');
        $this->setLocalization('ger', 'wishlist', 'wlDelete', 'Liste löschen');
        $this->setLocalization('eng', 'wishlist', 'wlDelete', 'Delete list');
        $this->setLocalization('ger', 'wishlist', 'wlRemoveAllProducts', 'Alle Artikel löschen');
        $this->setLocalization('eng', 'wishlist', 'wlRemoveAllProducts', 'Remove all products');
        $this->setLocalization('ger', 'wishlist', 'setAsStandardWishlist', 'Setzen Sie die aktuelle Wunschliste als Standard');
        $this->setLocalization('eng', 'wishlist', 'setAsStandardWishlist', 'Set wishlist as standard');
        $this->setLocalization('ger', 'wishlist', 'addNew', 'Neue erstellen');
        $this->setLocalization('eng', 'wishlist', 'addNew', 'Create new');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DELETE FROM tsprachsektion WHERE cName = "wishlist";');

        $this->removeLocalization('activate');
        $this->removeLocalization('wlDelete');
        $this->removeLocalization('wlRemoveAllProducts');
        $this->removeLocalization('setAsStandardWishlist');
        $this->removeLocalization('rename');
        $this->removeLocalization('copied');
        $this->removeLocalization('addNew');
    }
}
