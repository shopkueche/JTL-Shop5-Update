<?php
/**
 * Add password check lang
 *
 * @author mh
 * @created Thu, 4 July 2019 15:49:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190704154900
 */
class Migration_20190704154900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add password check lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'login', 'passwordTooShort', 'Das Passwort muss aus mindestens %s Zeichen bestehen.');
        $this->setLocalization('eng', 'login', 'passwordTooShort', 'The password must have at least %s characters.');
        $this->setLocalization('ger', 'login', 'passwordIsWeak', 'Schwach; versuchen Sie, Buchstaben und Zahlen zu kombinieren.');
        $this->setLocalization('eng', 'login', 'passwordIsWeak', 'Weak; try combining letters and numbers.');
        $this->setLocalization('ger', 'login', 'passwordIsMedium', 'Medium; versuchen Sie, Spezialzeichen zu verwenden.');
        $this->setLocalization('eng', 'login', 'passwordIsMedium', 'Medium; try using special characters.');
        $this->setLocalization('ger', 'login', 'passwordIsStrong', 'Starkes Passwort.');
        $this->setLocalization('eng', 'login', 'passwordIsStrong', 'Strong password.');
        $this->setLocalization('ger', 'login', 'passwordhasUsername', 'Das Passwort darf den Nutzernamen nicht enthalten.');
        $this->setLocalization('eng', 'login', 'passwordhasUsername', 'The password must not contain your username.');
        $this->setLocalization('ger', 'login', 'typeYourPassword', 'Geben Sie ein Passwort ein.');
        $this->setLocalization('eng', 'login', 'typeYourPassword', 'Enter a password.');

        $this->execute(
            "UPDATE teinstellungen
                SET cWert = GREATEST(cWert, 8)
                WHERE cName = 'kundenregistrierung_passwortlaenge'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('passwordTooShort');
        $this->removeLocalization('passwordIsWeak');
        $this->removeLocalization('passwordIsMedium');
        $this->removeLocalization('passwordIsStrong');
        $this->removeLocalization('passwordhasUsername');
        $this->removeLocalization('typeYourPassword');
    }
}
