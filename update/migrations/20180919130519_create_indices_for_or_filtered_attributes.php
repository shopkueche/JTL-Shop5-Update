<?php
/**
 * Create status table for or-filtered attributes
 *
 * @author fp
 * @created Wed, 19 Sep 2018 13:05:19 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180919130519
 */
class Migration_20180919130519 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create indices for or-filtered attributes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $duplicates = $this->getDB()->query(
            'SELECT kMerkmal, kMerkmalWert, kArtikel, COUNT(*) cntData
                FROM tartikelmerkmal
                GROUP BY kMerkmal, kMerkmalWert, kArtikel
                HAVING COUNT(*) > 1;',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($duplicates as $duplicate) {
            $this->getDB()->queryPrepared(
                'DELETE FROM tartikelmerkmal
                    WHERE kMerkmal = :attribID AND kMerkmalWert = :valueID AND kArtikel = :ProductID
                    LIMIT :delCount',
                [
                    'attribID'  => $duplicate->kMerkmal,
                    'valueID'   => $duplicate->kMerkmalWert,
                    'ProductID' => $duplicate->kArtikel,
                    'delCount'  => $duplicate->cntData - 1,
                ],
                ReturnType::DEFAULT
            );
        }
        $this->execute(
            'ALTER TABLE tartikelmerkmal ADD UNIQUE KEY kArtikelMerkmalWert_UQ (kArtikel, kMerkmalWert, kMerkmal)'
        );
        $this->execute(
            'ALTER TABLE tartikel ADD UNIQUE KEY kVaterArtikel_UQ (kArtikel, nIstVater, kVaterArtikel)'
        );
        $this->execute(
            'ALTER TABLE tkategorieartikel ADD UNIQUE KEY kKategorieArtikel_UQ (kArtikel, kKategorie)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tartikelmerkmal DROP INDEX kArtikelMerkmalWert_UQ');
        $this->execute('ALTER TABLE tartikel DROP INDEX kVaterArtikel_UQ');
        $this->execute('ALTER TABLE tkategorieartikel DROP INDEX kKategorieArtikel_UQ');
    }
}
