<?php
/**
 * add language variables for downloads
 *
 * @author ms
 * @created Thu, 13 Oct 2016 10:22:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161013102200
 */
class Migration_20161013102200 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('INSERT INTO tsprachsektion (cName) VALUES ("productDownloads");');

        $this->setLocalization('ger', 'productDownloads', 'downloadSection', 'Downloads');
        $this->setLocalization('eng', 'productDownloads', 'downloadSection', 'Downloads');

        $this->setLocalization('ger', 'productDownloads', 'downloadName', 'Name');
        $this->setLocalization('eng', 'productDownloads', 'downloadName', 'Name');

        $this->setLocalization('ger', 'productDownloads', 'downloadDescription', 'Beschreibung');
        $this->setLocalization('eng', 'productDownloads', 'downloadDescription', 'Description');

        $this->setLocalization('ger', 'productDownloads', 'downloadFileType', 'Dateiformat');
        $this->setLocalization('eng', 'productDownloads', 'downloadFileType', 'File type');

        $this->setLocalization('ger', 'productDownloads', 'downloadPreview', 'Vorschau');
        $this->setLocalization('eng', 'productDownloads', 'downloadPreview', 'Preview');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('downloadSection');
        $this->removeLocalization('downloadName');
        $this->removeLocalization('downloadDescription');
        $this->removeLocalization('downloadFileType');
        $this->removeLocalization('downloadPreview');
        $this->execute('DELETE FROM tsprachsektion WHERE cName = "productDownloads";');
    }
}
