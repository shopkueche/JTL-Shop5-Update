<?php
/**
 * change input types to password
 *
 * @author fm
 * @created Wed, 10 Nov 2016 10:11:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161110101100
 */
class Migration_20161110101100 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'pass' WHERE cWertName = 'newsletter_smtp_pass'");
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'pass' WHERE cWertName = 'caching_redis_pass'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'text' WHERE cWertName = 'newsletter_smtp_pass'");
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'text' WHERE cWertName = 'caching_redis_pass'");
    }
}
