<?php
/**
 * Create news lang table
 */

use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Update\DBMigrationHelper;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180801165500
 */
class Migration_20180801165500 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Create news language table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $db = $this->getDB();
        DBMigrationHelper::migrateToInnoDButf8('tnews');
        DBMigrationHelper::migrateToInnoDButf8('tnewskategorie');

        $this->execute(
            "CREATE TABLE `tnewssprache` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `kNews` INT(10) UNSIGNED NOT NULL,
              `languageID` INT NOT NULL,
              `languageCode` VARCHAR(5) NOT NULL,
              `title` VARCHAR(255) DEFAULT NULL,
              `content` LONGTEXT,
              `preview` LONGTEXT,
              `metaTitle` VARCHAR(255) NOT NULL DEFAULT '',
              `metaKeywords` VARCHAR(255) NOT NULL DEFAULT '',
              `metaDescription` VARCHAR(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              CONSTRAINT `fk_newsID`
                  FOREIGN KEY (`kNews`)
                  REFERENCES `tnews` (`kNews`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        $this->execute(
            "CREATE TABLE `tnewskategoriesprache` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `kNewsKategorie` INT(10) UNSIGNED NOT NULL,
              `languageID` INT NOT NULL,
              `languageCode` VARCHAR(5) NOT NULL,
              `name` VARCHAR(255) DEFAULT NULL,
              `description` TEXT,
              `metaTitle` VARCHAR(255) NOT NULL DEFAULT '',
              `metaDescription` VARCHAR(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              CONSTRAINT `fk_newscatID`
                  FOREIGN KEY (`kNewsKategorie`)
                  REFERENCES `tnewskategorie` (`kNewsKategorie`)
                  ON DELETE CASCADE
                  ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );
        $allNewsEntries = $db->query('SELECT * FROM tnews', ReturnType::ARRAY_OF_OBJECTS);
        foreach ($allNewsEntries as $newsEntry) {
            $new                  = new stdClass();
            $new->kNews           = (int)$newsEntry->kNews;
            $new->languageID      = (int)$newsEntry->kSprache;
            $new->languageCode    = Shop::Lang()->getIsoFromLangID($new->languageID)->cISO ?? 'ger';
            $new->title           = $newsEntry->cBetreff;
            $new->content         = $newsEntry->cText;
            $new->preview         = $newsEntry->cVorschauText;
            $new->metaTitle       = $newsEntry->cMetaTitle;
            $new->metaKeywords    = $newsEntry->cMetaKeywords;
            $new->metaDescription = $newsEntry->cMetaDescription;
            $db->insert('tnewssprache', $new);
        }
        $allNewsCategories = $db->query('SELECT * FROM tnewskategorie', ReturnType::ARRAY_OF_OBJECTS);
        foreach ($allNewsCategories as $newsCategory) {
            $new                  = new stdClass();
            $new->kNewsKategorie  = (int)$newsCategory->kNewsKategorie;
            $new->languageID      = (int)$newsCategory->kSprache;
            $new->languageCode    = Shop::Lang()->getIsoFromLangID($new->languageID)->cISO ?? 'ger';
            $new->name            = $newsCategory->cName;
            $new->description     = $newsCategory->cBeschreibung;
            $new->metaTitle       = $newsCategory->cMetaTitle;
            $new->metaDescription = $newsCategory->cMetaDescription;
            $db->insert('tnewskategoriesprache', $new);
        }
        $this->execute(
            'ALTER TABLE tnews 
            DROP COLUMN kSprache, 
            DROP COLUMN cBetreff, 
            DROP COLUMN cText, 
            DROP COLUMN cVorschauText, 
            DROP COLUMN cMetaDescription, 
            DROP COLUMN cMetaKeywords,
            DROP COLUMN cMetaTitle,
            DROP COLUMN cSeo'
        );
        $this->execute(
            'ALTER TABLE tnewskategorie
            DROP COLUMN kSprache, 
            DROP COLUMN cSeo, 
            DROP COLUMN cName, 
            DROP COLUMN cBeschreibung, 
            DROP COLUMN cMetaTitle, 
            DROP COLUMN cMetaDescription'
        );
        $this->execute(
            'ALTER TABLE tnewskategorie
            ADD COLUMN lft INT NOT NULL DEFAULT 0,
            ADD COLUMN rght INT NOT NULL DEFAULT 0,
            ADD COLUMN lvl INT NOT NULL DEFAULT 0,
            ADD INDEX lft_rght (`lft`, `rght`)'
        );
        $this->rebuildCategoryTree($db, 0, 1);
        $this->execute('DELETE FROM tspezialseite WHERE nLinkart = 20');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE tnewssprache');
        $this->execute('DROP TABLE tnewskategoriesprache');
        $this->execute(
            'ALTER TABLE tnewskategorie
            ADD COLUMN `kSprache` int(10) unsigned NOT NULL,
            ADD COLUMN `cSeo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cBeschreibung` mediumtext COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cMetaTitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cMetaDescription` varchar(255) COLLATE utf8_unicode_ci NOT NULL'
        );
        $this->execute(
            'ALTER TABLE tnews 
            ADD COLUMN `kSprache` int(10) unsigned NOT NULL,
            ADD COLUMN `cSeo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cBetreff` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cText` mediumtext COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cVorschauText` mediumtext COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cMetaTitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cMetaDescription` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `cMetaKeywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL'
        );
        $this->execute(
            "INSERT INTO tspezialseite (kSpezialseite, kPlugin, cName, cDateiname, nLinkart, nSort)
                VALUES (16, 0, 'Newsarchiv', NULL, 20, 20)"
        );
    }

    /**
     * update lft/rght values for categories in the nested set model
     *
     * @param \JTL\DB\DbInterface $db
     * @param int                 $parent_id
     * @param int                 $left
     * @param int                 $level
     * @return int
     */
    private function rebuildCategoryTree($db, int $parent_id, int $left, int $level = 0): int
    {
        $right  = $left + 1;
        $result = $db->selectAll(
            'tnewskategorie',
            'kParent',
            $parent_id,
            'kNewsKategorie',
            'nSort, kNewsKategorie'
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree($db, (int)$_res->kNewsKategorie, $right, $level + 1);
        }
        $db->update('tnewskategorie', 'kNewsKategorie', $parent_id, (object)[
            'lft'  => $left,
            'rght' => $right,
            'lvl'  => $level,
        ]);

        return $right + 1;
    }
}
