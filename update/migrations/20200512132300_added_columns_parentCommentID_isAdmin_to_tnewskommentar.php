<?php
/**Added isAdmin, parentCommentID columns to tnewskommentar table*/

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200512132300
 */
class Migration_20200512132300 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Added isAdmin, parentCommentID columns to tnewskommentar table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE `tnewskommentar`
                    ADD `parentCommentID` int(10) unsigned NOT NULL DEFAULT '0' AFTER `cKommentar`,
                    ADD `isAdmin` int(10) unsigned NOT NULL DEFAULT '0' AFTER `parentCommentID`;"
        );
        $this->setLocalization('ger', 'news', 'commentReply', 'Antwort');
        $this->setLocalization('eng', 'news', 'commentReply', 'Reply');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('commentReply','news');
        $this->execute(
            "ALTER TABLE `tnewskommentar` DROP `isAdmin`, DROP `parentCommentID`;"
        );
    }
}
