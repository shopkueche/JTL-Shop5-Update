<?php

namespace JTL\Extensions\Download;

use DateTime;
use JTL\Cart\Cart;
use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;

/**
 * Class Download
 * @package JTL\Extensions\Download
 */
class Download
{
    public const ERROR_NONE = 1;

    public const ERROR_ORDER_NOT_FOUND = 2;

    public const ERROR_INVALID_CUSTOMER = 3;

    public const ERROR_PRODUCT_NOT_FOUND = 4;

    public const ERROR_DOWNLOAD_LIMIT_REACHED = 5;

    public const ERROR_DOWNLOAD_EXPIRED = 6;

    public const ERROR_MISSING_PARAMS = 7;

    /**
     * @var int
     */
    public $kDownload;

    /**
     * @var string
     */
    public $cID;

    /**
     * @var string
     */
    public $cPfad;

    /**
     * @var string
     */
    public $cPfadVorschau;

    /**
     * @var int
     */
    public $nAnzahl;

    /**
     * @var int
     */
    public $nTage;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var object
     */
    public $oDownloadSprache;

    /**
     * @var array
     */
    public $oDownloadHistory_arr;

    /**
     * @var int
     */
    public $kBestellung;

    /**
     * @var string
     */
    public $dGueltigBis;

    /**
     * @var array
     */
    public $oArtikelDownload_arr;

    /**
     * @var bool
     */
    private $licenseOK;

    /**
     * Download constructor.
     * @param int  $id
     * @param int  $languageID
     * @param bool $info
     * @param int  $orderID
     */
    public function __construct(int $id = 0, int $languageID = 0, bool $info = true, int $orderID = 0)
    {
        $this->licenseOK = self::checkLicense();
        if ($id > 0 && $this->licenseOK === true) {
            $this->loadFromDB($id, $languageID, $info, $orderID);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_DOWNLOADS);
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param bool $info
     * @param int  $orderID
     */
    private function loadFromDB(int $id, int $languageID, bool $info, int $orderID): void
    {
        $item = Shop::Container()->getDB()->select('tdownload', 'kDownload', $id);
        if ($item !== null && isset($item->kDownload) && (int)$item->kDownload > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kDownload = (int)$this->kDownload;
            $this->nAnzahl   = (int)$this->nAnzahl;
            $this->nTage     = (int)$this->nTage;
            $this->nSort     = (int)$this->nSort;
            if ($info) {
                if (!$languageID) {
                    $languageID = Shop::getLanguageID();
                }
                $this->oDownloadSprache     = new Localization($item->kDownload, $languageID);
                $this->oDownloadHistory_arr = History::getHistory($item->kDownload);
            }
            if ($orderID > 0) {
                $this->kBestellung = $orderID;
                $order             = Shop::Container()->getDB()->select(
                    'tbestellung',
                    'kBestellung',
                    $orderID,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'kBestellung, dBezahltDatum'
                );
                if ($order !== null
                    && $order->kBestellung > 0
                    && $order->dBezahltDatum !== null
                    && $this->getTage() > 0
                ) {
                    $paymentDate = new DateTime($order->dBezahltDatum);
                    $modifyBy    = $this->getTage() + 1;
                    $paymentDate->modify('+' . $modifyBy . ' day');
                    $this->dGueltigBis = $paymentDate->format('d.m.Y');
                }
            }
            $this->oArtikelDownload_arr = Shop::Container()->getDB()->queryPrepared(
                'SELECT tartikeldownload.*
                    FROM tartikeldownload
                    JOIN tdownload 
                        ON tdownload.kDownload = tartikeldownload.kDownload
                    WHERE tartikeldownload.kDownload = :dlid
                    ORDER BY tdownload.nSort',
                ['dlid' => $this->kDownload],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function save(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function delete(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param array $keys
     * @param int   $languageID
     * @return array
     */
    public static function getDownloads(array $keys = [], int $languageID = 0): array
    {
        $productID  = isset($keys['kArtikel']) ? (int)$keys['kArtikel'] : 0;
        $orderID    = isset($keys['kBestellung']) ? (int)$keys['kBestellung'] : 0;
        $customerID = isset($keys['kKunde']) ? (int)$keys['kKunde'] : 0;
        $downloads  = [];
        if (($productID > 0 || $orderID > 0 || $customerID > 0) && $languageID > 0 && self::checkLicense()) {
            $select = 'tartikeldownload.kDownload';
            $where  = 'kArtikel = ' . $productID;
            $join   = 'LEFT JOIN tdownload ON tartikeldownload.kDownload = tdownload.kDownload';
            if ($orderID > 0) {
                $select = 'tbestellung.kBestellung, tbestellung.kKunde, tartikeldownload.kDownload';
                $where  = 'tartikeldownload.kArtikel = twarenkorbpos.kArtikel';
                $join   = 'JOIN tbestellung ON tbestellung.kBestellung = ' . $orderID . '
                               JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                               JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                    AND twarenkorbpos.nPosTyp = ' . \C_WARENKORBPOS_TYP_ARTIKEL;
            } elseif ($customerID > 0) {
                $select = 'MAX(tbestellung.kBestellung) AS kBestellung, tbestellung.kKunde, 
                    tartikeldownload.kDownload';
                $where  = 'tartikeldownload.kArtikel = twarenkorbpos.kArtikel';
                $join   = 'JOIN tbestellung ON tbestellung.kKunde = ' . $customerID . '
                               JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                               JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                    AND twarenkorbpos.nPosTyp = ' . \C_WARENKORBPOS_TYP_ARTIKEL;
            }
            $items = Shop::Container()->getDB()->query(
                'SELECT ' . $select . '
                    FROM tartikeldownload
                    ' . $join . '
                    WHERE ' . $where . '
                    GROUP BY tartikeldownload.kDownload
                    ORDER BY tdownload.nSort, tdownload.dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($items as $i => $download) {
                $download->kDownload = (int)$download->kDownload;
                $downloads[$i]       = new self(
                    $download->kDownload,
                    $languageID,
                    true,
                    (int)($download->kBestellung ?? 0)
                );
                if (($orderID > 0 || $customerID > 0) && $downloads[$i]->getAnzahl() > 0) {
                    $download->kKunde      = (int)$download->kKunde;
                    $download->kBestellung = (int)$download->kBestellung;

                    $history                    = History::getOrderHistory(
                        $download->kKunde,
                        $download->kBestellung
                    );
                    $id                         = $downloads[$i]->getDownload();
                    $count                      = isset($history[$id])
                        ? \count($history[$id])
                        : 0;
                    $downloads[$i]->cLimit      = $count . ' / ' . $downloads[$i]->getAnzahl();
                    $downloads[$i]->kBestellung = $download->kBestellung;
                }
            }
        }

        return $downloads;
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    public static function hasDownloads($cart): bool
    {
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && isset($item->Artikel->oDownload_arr)
                && \count($item->Artikel->oDownload_arr) > 0
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $downloadID
     * @param int $customerID
     * @param int $orderID
     * @return int
     */
    public static function getFile(int $downloadID, int $customerID, int $orderID): int
    {
        if ($downloadID > 0 && $customerID > 0 && $orderID > 0) {
            $download = new self($downloadID, 0, false);
            $res      = $download::checkFile($download->kDownload, $customerID, $orderID);
            if ($res === self::ERROR_NONE) {
                (new History())
                    ->setDownload($downloadID)
                    ->setKunde($customerID)
                    ->setBestellung($orderID)
                    ->setErstellt('NOW()')
                    ->save();

                self::send_file_to_browser(
                    \PFAD_DOWNLOADS . $download->getPfad(),
                    'application/octet-stream'
                );
            }

            return $res;
        }

        return self::ERROR_MISSING_PARAMS;
    }

    /**
     * Fehlercodes:
     * 1 = Alles O.K.
     * 2 = Bestellung nicht gefunden
     * 3 = Kunde stimmt nicht
     * 4 = Kein Artikel mit Downloads gefunden
     * 5 = Maximales Downloadlimit wurde erreicht
     * 6 = Maximales Datum wurde erreicht
     * 7 = Paramter fehlen
     *
     * @param int $downloadID
     * @param int $customerID
     * @param int $orderID
     * @return int
     */
    public static function checkFile(int $downloadID, int $customerID, int $orderID): int
    {
        if ($downloadID > 0 && $customerID > 0 && $orderID > 0) {
            $order = new Bestellung($orderID);
            // Existiert die Bestellung und wurde Sie bezahlt?
            if ($order->kBestellung <= 0 || empty($order->dBezahltDatum) || $order->dBezahltDatum === null) {
                return self::ERROR_ORDER_NOT_FOUND;
            }
            // Stimmt der Kunde?
            if ((int)$order->kKunde !== $customerID) {
                return self::ERROR_INVALID_CUSTOMER;
            }
            $order->fuelleBestellung();
            $download = new self($downloadID, 0, false);
            // Gibt es einen Artikel der zum Download passt?
            if (!\is_array($download->oArtikelDownload_arr) || \count($download->oArtikelDownload_arr) === 0) {
                return self::ERROR_PRODUCT_NOT_FOUND;
            }
            foreach ($order->Positionen as $item) {
                foreach ($download->oArtikelDownload_arr as $donwloadItem) {
                    if ($item->kArtikel != $donwloadItem->kArtikel) {
                        continue;
                    }
                    // Check Anzahl
                    if ($download->getAnzahl() > 0) {
                        $history = History::getOrderHistory($customerID, $orderID);
                        if (\count($history[$download->kDownload]) >= $download->getAnzahl()) {
                            return self::ERROR_DOWNLOAD_LIMIT_REACHED;
                        }
                    }
                    // Check Datum
                    $paymentDate = new DateTime($order->dBezahltDatum);
                    $paymentDate->modify('+' . ($download->getTage() + 1) . ' day');
                    if ($download->getTage() > 0 && $paymentDate < new DateTime()) {
                        return self::ERROR_DOWNLOAD_EXPIRED;
                    }

                    return self::ERROR_NONE;
                }
            }
        }

        return self::ERROR_MISSING_PARAMS;
    }

    /**
     * Fehlercodes:
     * 2 = Bestellung nicht gefunden
     * 3 = Kunde stimmt nicht
     * 4 = Kein Artikel mit Downloads gefunden
     * 5 = Maximales Downloadlimit wurde erreicht
     * 6 = Maximales Datum wurde erreicht
     * 7 = Paramter fehlen
     *
     * @param int $errorCode
     * @return string
     */
    public static function mapGetFileErrorCode(int $errorCode): string
    {
        switch ($errorCode) {
            case self::ERROR_ORDER_NOT_FOUND: // Bestellung nicht gefunden
                $error = Shop::Lang()->get('dlErrorOrderNotFound');
                break;
            case self::ERROR_INVALID_CUSTOMER: // Kunde stimmt nicht
                $error = Shop::Lang()->get('dlErrorCustomerNotMatch');
                break;
            case self::ERROR_PRODUCT_NOT_FOUND: // Kein Artikel mit Downloads gefunden
                $error = Shop::Lang()->get('dlErrorDownloadNotFound');
                break;
            case self::ERROR_DOWNLOAD_LIMIT_REACHED: // Maximales Downloadlimit wurde erreicht
                $error = Shop::Lang()->get('dlErrorDownloadLimitReached');
                break;
            case self::ERROR_DOWNLOAD_EXPIRED: // Maximales Datum wurde erreicht
                $error = Shop::Lang()->get('dlErrorValidityReached');
                break;
            case self::ERROR_MISSING_PARAMS: // Paramter fehlen
                $error = Shop::Lang()->get('dlErrorWrongParameter');
                break;
            default:
                $error = '';
                break;
        }

        return $error;
    }

    /**
     * @param int $kDownload
     * @return $this
     */
    public function setDownload(int $kDownload): self
    {
        $this->kDownload = $kDownload;

        return $this;
    }

    /**
     * @param string $cID
     * @return $this
     */
    public function setID($cID): self
    {
        $this->cID = $cID;

        return $this;
    }

    /**
     * @param string $cPfad
     * @return $this
     */
    public function setPfad($cPfad): self
    {
        $this->cPfad = $cPfad;

        return $this;
    }

    /**
     * @param string $cPfadVorschau
     * @return $this
     */
    public function setPfadVorschau($cPfadVorschau): self
    {
        $this->cPfadVorschau = $cPfadVorschau;

        return $this;
    }

    /**
     * @param int $nAnzahl
     * @return $this
     */
    public function setAnzahl(int $nAnzahl): self
    {
        $this->nAnzahl = $nAnzahl;

        return $this;
    }

    /**
     * @param int $nTage
     * @return $this
     */
    public function setTage(int $nTage): self
    {
        $this->nTage = $nTage;

        return $this;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort): self
    {
        $this->nSort = $sort;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = $dErstellt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDownload(): ?int
    {
        return $this->kDownload;
    }

    /**
     * @return string|null
     */
    public function getID(): ?string
    {
        return $this->cID;
    }

    /**
     * @return string|null
     */
    public function getPfad(): ?string
    {
        return $this->cPfad;
    }

    /**
     * @return bool
     */
    public function hasPreview(): bool
    {
        return \mb_strlen($this->cPfadVorschau) > 0;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        if (\mb_strlen($this->cPfad) > 0) {
            $pathInfo = \pathinfo($this->cPfad);
            if (\is_array($pathInfo)) {
                return \mb_convert_case($pathInfo['extension'], \MB_CASE_UPPER);
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPreviewExtension(): string
    {
        if (\mb_strlen($this->cPfadVorschau) > 0) {
            $pathInfo = \pathinfo($this->cPfadVorschau);
            if (\is_array($pathInfo)) {
                return \mb_convert_case($pathInfo['extension'], \MB_CASE_UPPER);
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPreviewType(): string
    {
        switch (\strtolower($this->getPreviewExtension())) {
            case 'mpeg':
            case 'mpg':
            case 'avi':
            case 'wmv':
            case 'mp4':
                return 'video';

            case 'wav':
            case 'mp3':
            case 'wma':
                return 'music';

            case 'gif':
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'jpe':
            case 'bmp':
                return 'image';
            default:
                break;
        }

        return 'misc';
    }

    /**
     * @return string
     */
    public function getPreview(): string
    {
        return Shop::getURL() . '/' . \PFAD_DOWNLOADS_PREVIEW_REL . $this->cPfadVorschau;
    }

    /**
     * @return int|null
     */
    public function getAnzahl()
    {
        return $this->nAnzahl;
    }

    /**
     * @return int|null
     */
    public function getTage()
    {
        return $this->nTage;
    }

    /**
     * @return int|null
     */
    public function getSort()
    {
        return $this->nSort;
    }

    /**
     * @return string|null
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @param string $filename
     * @param string $mimetype
     */
    private static function send_file_to_browser(string $filename, string $mimetype): void
    {
        $browser   = 'other';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (\preg_match('/Opera\/([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'opera';
        } elseif (\preg_match('/MSIE ([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'ie';
        }
        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = ($browser === 'ie' || $browser === 'opera')
                ? 'application/octetstream'
                : 'application/octet-stream';
        }

        @\ob_end_clean();
        @\ini_set('zlib.output_compression', 'Off');

        \header('Pragma: public');
        \header('Content-Transfer-Encoding: none');
        if ($browser === 'ie') {
            \header('Content-Type: ' . $mimetype);
            \header('Content-Disposition: inline; filename="' . \basename($filename) . '"');
        } else {
            \header('Content-Type: ' . $mimetype . '; name="' . \basename($filename) . '"');
            \header('Content-Disposition: attachment; filename="' . \basename($filename) . '"');
        }

        $size = @\filesize($filename);
        if ($size) {
            \header('Content-length: ' . $size);
        }

        \readfile($filename);
        exit;
    }
}
