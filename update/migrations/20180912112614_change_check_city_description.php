<?php
/**
 * change_check_city_description
 *
 * @author mh
 * @created Wed, 12 Sep 2018 11:26:14 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180912112614
 */
class Migration_20180912112614 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Change check city description';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Fehlermeldung ausgeben, wenn eingegebene Stadt eine Zahl enthält." WHERE cWertName="kundenregistrierung_pruefen_ort";');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Wenn die eingegebene Stadt eine Zahle enthät abbrechen" WHERE cWertName="kundenregistrierung_pruefen_ort";');
    }
}
