<?php
/** fix typo in lang var */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171214175900
 */
class Migration_20171214175900 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE tsprachwerte 
                SET cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufentscheidung'
                WHERE cName = 'firstReview' 
                AND cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufenscheidung'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "UPDATE tsprachwerte 
                SET cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufenscheidung' 
                WHERE cName = 'firstReview' 
                AND cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufentscheidung'"
        );
    }
}
