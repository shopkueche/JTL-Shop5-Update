<?php
/**
 * new category structure
 *
 * @author fm
 * @created Mo, 11 Apr 2016 09:31:13 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160411093113
 */
class Migration_20160411093113 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * update lft/rght values for categories in the nested set model
     *
     * @param int $parent_id
     * @param int $left
     * @return int
     */
    private function rebuildCategoryTree(int $parent_id, int $left): int
    {
        // the right value of this node is the left value + 1
        $right = $left + 1;
        // get all children of this node
        $result = $this->getDB()->query(
            'SELECT kKategorie 
                FROM tkategorie 
                WHERE kOberKategorie = ' . $parent_id . ' 
                ORDER BY nSort, cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree((int)$_res->kKategorie, $right);
        }
        // we've got the left value, and now that we've processed the children of this node we also know the right value
        $this->execute(
            'UPDATE tkategorie 
                SET lft = ' . $left . ', rght = ' . $right . ' 
                WHERE kKategorie = ' . $parent_id
        );

        // return the right value of this node + 1
        return $right + 1;
    }

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->rebuildCategoryTree(0, 1);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('UPDATE `tkategorie` SET `lft` = 0, `rght` = 0;');
    }
}
