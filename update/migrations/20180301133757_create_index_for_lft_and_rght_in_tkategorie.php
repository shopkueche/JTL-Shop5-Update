<?php
/**
 * Create index for lft and rght in tkategorie
 *
 * @author fp
 * @created Thu, 01 Mar 2018 13:37:57 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180301133757
 */
class Migration_20180301133757 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for lft and rght in tkategorie';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        // Check if an index for lft or rght always exists
        $idxExists = $this->fetchAll(
            "SHOW INDEX FROM tkategorie WHERE Column_name IN ('lft', 'rght')"
        );

        if (count($idxExists) > 0) {
            // If so - delete it...
            $idxDelete = [];
            foreach ($idxExists as $idx) {
                $idxDelete[] = $idx->Key_name;
            }
            foreach (array_unique($idxDelete) as $idxName) {
                $this->execute(
                    "ALTER TABLE `tkategorie` 
                        DROP INDEX `$idxName`"
                );
            }
        }

        $this->execute(
            'ALTER TABLE `tkategorie` 
                ADD INDEX `idx_tkategorie_lft_rght` (`lft` ASC, `rght` ASC);'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE `tkategorie` 
                DROP INDEX `idx_tkategorie_lft_rght`'
        );
    }
}
