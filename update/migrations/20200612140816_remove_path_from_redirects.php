<?php declare(strict_types=1);
/**
 * Remove path from redirects
 *
 * @author fp
 * @created Fri, 12 Jun 2020 14:08:16 +0200
 */

use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200612140816
 */
class Migration_20200612140816 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Remove path from redirects';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $shopSubPath = trim(parse_url(Shop::getURL(), PHP_URL_PATH) ?? '', '/') . '/';
        if (strlen($shopSubPath) > 1) {
            // remove Shop-URL path from redirection source
            $this->db->executeQueryPrepared(
                "UPDATE tredirect
                    SET cFromUrl = REPLACE(cFromUrl, :path, '')
                    WHERE cFromUrl LIKE :searchPath",
                [
                    'path'       => $shopSubPath,
                    'searchPath' => '/' . $shopSubPath . '%'
                ],
                ReturnType::DEFAULT
            );
            // delete all redirects where source and destination are equal
            $this->db->executeQuery(
                'DELETE FROM tredirect WHERE cFromUrl = cToUrl',
                ReturnType::DEFAULT
            );
            // delete not found records with existing redirection
            $this->db->executeQuery(
                "DELETE t1 FROM tredirect t1
                    INNER JOIN tredirect t2 ON t2.cFromUrl = t1.cFromUrl
                                           AND t2.kRedirect != t1.kRedirect
                    WHERE t1.cToUrl = '';",
                ReturnType::DEFAULT
            );
            // delete all duplicate redirects
            $this->db->executeQuery(
                'DELETE t1 FROM tredirect t1
                    INNER JOIN tredirect t2 ON t2.cFromUrl = t1.cFromUrl
                                           AND t2.kRedirect > t1.kRedirect;',
                ReturnType::DEFAULT
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
