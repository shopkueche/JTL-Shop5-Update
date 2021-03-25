<?php declare(strict_types=1);
/**
 * Refactor tstoreauth
 *
 * @author fp
 * @created Mon, 23 Mar 2020 15:56:13 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 */
class Migration_20200323155613 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Refactor tstoreauth';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tstoreauth
                ADD owner       INT             NOT NULL FIRST,
                ADD verified    VARCHAR(128)    NOT NULL,
                ADD CONSTRAINT tstoreauth_pk PRIMARY KEY (owner)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tstoreauth DROP owner'
        );
        $this->execute(
            'ALTER TABLE tstoreauth DROP verified'
        );
    }
}
