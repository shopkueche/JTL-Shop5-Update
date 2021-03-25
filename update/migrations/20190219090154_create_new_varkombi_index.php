<?php
/**
 * Create new Varkombi index
 *
 * @author fp
 * @created Tue, 19 Feb 2019 09:01:54 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190219090154
 */
class Migration_20190219090154 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create new Varkombi index';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE UNIQUE INDEX idx_eigenschaftwert_uq
                ON teigenschaftkombiwert (kEigenschaft, kEigenschaftWert, kEigenschaftKombi)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'DROP INDEX idx_eigenschaftwert_uq ON teigenschaftkombiwert'
        );
    }
}
