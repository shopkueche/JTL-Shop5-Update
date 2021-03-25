<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: mo
 * Date: 2019-02-13
 * Time: 15:37
 */

namespace JTL\dbeS;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Shop;

/**
 * Class Test
 * @package JTL\dbeS
 */
class Test
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Test constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function execute(): string
    {
        if (Request::postInt('wawiversion') < \JTL_MIN_WAWI_VERSION) {
            \syncException(
                'Ihr JTL-Shop Version ' . \APPLICATION_VERSION .
                ' benötigt für den Datenabgleich mindestens JTL-Wawi Version ' . (\JTL_MIN_WAWI_VERSION / 100000.0) .
                ". \nEine aktuelle Version erhalten Sie unter: https://jtl-url.de/wawidownload",
                \FREIDEFINIERBARER_FEHLER
            );
        }
        $versionStr = null;
        if (Request::postInt('kKunde') > 0) {
            $state = $this->db->query(
                "SHOW TABLE STATUS LIKE 'tkunde'",
                ReturnType::SINGLE_OBJECT
            );
            if ($state->Auto_increment < (int)$_POST['kKunde']) {
                $this->db->query(
                    'ALTER TABLE tkunde AUTO_INCREMENT = ' . (int)$_POST['kKunde'],
                    ReturnType::DEFAULT
                );
            }
        }
        if (Request::postInt('kBestellung') > 0) {
            $state = $this->db->query(
                "SHOW TABLE STATUS LIKE 'tbestellung'",
                ReturnType::SINGLE_OBJECT
            );
            if ($state->Auto_increment < (int)$_POST['kBestellung']) {
                $this->db->query(
                    'ALTER TABLE tbestellung AUTO_INCREMENT = ' . (int)$_POST['kBestellung'],
                    ReturnType::DEFAULT
                );
            }
        }
        if (Request::postInt('kLieferadresse') > 0) {
            $state = $this->db->query(
                "SHOW TABLE STATUS LIKE 'tlieferadresse'",
                ReturnType::SINGLE_OBJECT
            );
            if ($state->Auto_increment < (int)$_POST['kLieferadresse']) {
                $this->db->query(
                    'ALTER TABLE tlieferadresse AUTO_INCREMENT = ' . (int)$_POST['kLieferadresse'],
                    ReturnType::DEFAULT
                );
            }
        }
        if (Request::postInt('kZahlungseingang') > 0) {
            $state = $this->db->query(
                "SHOW TABLE STATUS LIKE 'tzahlungseingang'",
                ReturnType::SINGLE_OBJECT
            );
            if ($state->Auto_increment < (int)$_POST['kZahlungseingang']) {
                $this->db->query(
                    'ALTER TABLE tzahlungseingang AUTO_INCREMENT  = ' . (int)$_POST['kZahlungseingang'],
                    ReturnType::DEFAULT
                );
            }
        }
        $version    = Shop::getShopDatabaseVersion();
        $versionStr = \sprintf('%d%02d', $version->getMajor(), $version->getMinor());

        return '0;JTL4;' . $versionStr . ';';
    }
}
