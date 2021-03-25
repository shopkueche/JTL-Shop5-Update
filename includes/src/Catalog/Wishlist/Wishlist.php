<?php

namespace JTL\Catalog\Wishlist;

use Exception;
use Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\Campaign;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use stdClass;

/**
 * Class Wishlist
 * @package JTL\Catalog\Wishlist
 */
class Wishlist
{
    /**
     * @var int
     */
    public $kWunschliste;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $nStandard;

    /**
     * @var int
     */
    public $nOeffentlich;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cURLID;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * @var array
     */
    public $CWunschlistePos_arr = [];

    /**
     * @var Customer
     */
    public $oKunde;

    /**
     * Wishlist constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->ladeWunschliste($id);
        } else {
            $this->reset();
        }
    }

    /**
     * @param int $wishlistID
     * @return self
     * @since  5.0.0
     */
    public static function instanceByID(int $wishlistID): self
    {
        return new self($wishlistID);
    }

    /**
     * @param string $urlID
     * @return self
     * @since 5.0.0
     */
    public static function instanceByURLID(string $urlID): self
    {
        $instance = new self();
        $data     = Shop::Container()->getDB()->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM twunschliste
                WHERE cURLID = :wlID
                    AND nOeffentlich = 1",
            ['wlID' => $urlID],
            ReturnType::SINGLE_OBJECT
        );

        return $data ? $instance->setRecord($data) : $instance;
    }

    /**
     * @param int $customerID
     * @return self
     * @since 5.0.0
     */
    public static function instanceByCustomerID(int $customerID): self
    {
        $instance = new self();
        $data     = Shop::Container()->getDB()->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM twunschliste
                WHERE kKunde = :customerID
                    AND nStandard = 1",
            ['customerID' => $customerID],
            ReturnType::SINGLE_OBJECT
        );

        return $data ? $instance->setRecord($data) : $instance->schreibeDB();
    }

    /**
     * @return Wishlist
     * @since  5.0.0
     */
    private function reset(): self
    {
        $this->kWunschliste        = 0;
        $this->kKunde              = Frontend::getCustomer()->getID() ?? 0;
        $this->nStandard           = 1;
        $this->nOeffentlich        = 0;
        $this->cName               = Shop::Lang()->get('wishlist');
        $this->dErstellt           = 'NOW()';
        $this->cURLID              = '';
        $this->CWunschlistePos_arr = [];

        return $this;
    }

    /**
     * @param object $wishlist
     * @return bool
     * @since  5.0.0
     */
    private function validate(object $wishlist): bool
    {
        $wishlistID = self::checkeParameters();
        $customerID = Frontend::getCustomer()->getID() ?? 0;

        return ($customerID > 0 && $customerID === (int)$wishlist->kKunde)
            || ($wishlistID > 0 && $wishlistID === (int)$wishlist->kWunschliste);
    }

    /**
     * fügt eine Position zur Wunschliste hinzu
     *
     * @param int    $productID
     * @param string $productName
     * @param array  $attributes
     * @param float  $qty
     * @return int
     */
    public function fuegeEin(int $productID, string $productName, array $attributes, $qty): int
    {
        $exists = false;
        $index  = 0;
        foreach ($this->CWunschlistePos_arr as $i => $item) {
            $item->kArtikel = (int)$item->kArtikel;
            if ($exists) {
                break;
            }

            if ($item->kArtikel === $productID) {
                $index  = $i;
                $exists = true;
                if (\count($item->CWunschlistePosEigenschaft_arr) > 0) {
                    foreach ($attributes as $attr) {
                        if (!$item->istEigenschaftEnthalten($attr->kEigenschaft, $attr->kEigenschaftWert)) {
                            $exists = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($exists) {
            $this->CWunschlistePos_arr[$index]->fAnzahl += $qty;
            $this->CWunschlistePos_arr[$index]->updateDB();
            $kWunschlistePos = (int)$this->CWunschlistePos_arr[$index]->kWunschlistePos;
        } else {
            $item                = new WishlistItem(
                $productID,
                $productName,
                $qty,
                $this->kWunschliste
            );
            $item->dHinzugefuegt = \date('Y-m-d H:i:s');
            $item->schreibeDB();
            $kWunschlistePos = (int)$item->kWunschlistePos;
            $item->erstellePosEigenschaften($attributes);
            $product = new Artikel();
            try {
                $product->fuelleArtikel($productID, Artikel::getDefaultOptions());
                $item->Artikel               = $product;
                $this->CWunschlistePos_arr[] = $item;
            } catch (Exception $e) {
            }
        }

        \executeHook(\HOOK_WUNSCHLISTE_CLASS_FUEGEEIN);

        return $kWunschlistePos;
    }

    /**
     * @param int $itemID
     * @return $this
     */
    public function entfernePos(int $itemID): self
    {
        $customer = Shop::Container()->getDB()->queryPrepared(
            'SELECT twunschliste.kKunde
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                WHERE twunschlistepos.kWunschlistePos = :wliid',
            ['wliid' => $itemID],
            ReturnType::SINGLE_OBJECT
        );
        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WunschlistenPos ist
        if (!empty($customer->kKunde) && (int)$customer->kKunde === Frontend::getCustomer()->getID()) {
            // Alle Eigenschaften löschen
            Shop::Container()->getDB()->delete('twunschlisteposeigenschaft', 'kWunschlistePos', $itemID);
            // Die Posiotion mit ID $kWunschlistePos löschen
            Shop::Container()->getDB()->delete('twunschlistepos', 'kWunschlistePos', $itemID);
            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $wlPosition) {
                if ((int)$wlPosition->kWunschlistePos === $itemID) {
                    unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                }
            }

            // Positionen Array in der Wunschliste neu nummerieren
            $_SESSION['Wunschliste']->CWunschlistePos_arr = \array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function entferneAllePos(): int
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE twunschlistepos, twunschlisteposeigenschaft 
                FROM twunschlistepos
                LEFT JOIN twunschlisteposeigenschaft 
                    ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                WHERE twunschlistepos.kWunschliste = :wlID',
            ['wlID' => (int)$this->kWunschliste],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
     * Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
     *
     * @param int   $wishlistID
     * @param array $items
     * @return bool|int
     */
    public static function pruefeArtikelnachBestellungLoeschen(int $wishlistID, array $items)
    {
        $conf = Shop::getSettings([\CONF_GLOBAL]);
        if ($wishlistID < 1 || $conf['global']['global_wunschliste_artikel_loeschen_nach_kauf'] !== 'Y') {
            return false;
        }
        $count    = 0;
        $wishlist = new self($wishlistID);
        if (!($wishlist->kWunschliste > 0
            && \is_array($items)
            && \count($wishlist->CWunschlistePos_arr) > 0
            && \count($items) > 0)
        ) {
            return false;
        }
        foreach ($wishlist->CWunschlistePos_arr as $item) {
            foreach ($items as $product) {
                if ((int)$item->kArtikel !== (int)$product->kArtikel) {
                    continue;
                }
                // mehrfache Variationen beachten
                if (!empty($item->CWunschlistePosEigenschaft_arr) && !empty($product->WarenkorbPosEigenschaftArr)) {
                    $matchesFound = 0;
                    $index        = 0;
                    foreach ($item->CWunschlistePosEigenschaft_arr as $wpAttr) {
                        if ($index === $matchesFound) {
                            foreach ($product->WarenkorbPosEigenschaftArr as $attr) {
                                if ((int)$wpAttr->kEigenschaftWert !== 0
                                    && $wpAttr->kEigenschaftWert === $attr->kEigenschaftWert
                                ) {
                                    ++$matchesFound;
                                    break;
                                }
                                if ($wpAttr->kEigenschaftWert === 0
                                    && $attr->kEigenschaftWert === 0
                                    && !empty($wpAttr->cFreifeldWert)
                                    && !empty($attr->cFreifeldWert)
                                    && $wpAttr->cFreifeldWert === $attr->cFreifeldWert
                                ) {
                                    ++$matchesFound;
                                    break;
                                }
                            }
                        }
                        ++$index;
                    }
                    if ($matchesFound === \count($product->WarenkorbPosEigenschaftArr)) {
                        $wishlist->entfernePos($item->kWunschlistePos);
                    }
                } else {
                    $wishlist->entfernePos($item->kWunschlistePos);
                }
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param string $query
     * @return array
     */
    public function sucheInWunschliste(string $query): array
    {
        if (empty($query)) {
            return [];
        }
        $db            = Shop::Container()->getDB();
        $searchResults = [];
        $data          = $db->queryPrepared(
            "SELECT twunschlistepos.*, date_format(twunschlistepos.dHinzugefuegt, '%d.%m.%Y %H:%i') AS dHinzugefuegt_de
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschlistepos.kWunschliste = twunschliste.kWunschliste
                    AND (twunschlistepos.cArtikelName LIKE :search
                    OR twunschlistepos.cKommentar LIKE :search)
                WHERE twunschliste.kWunschliste = :wlID",
            [
                'search' => '%' . $query . '%',
                'wlID'   => (int)$this->kWunschliste
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $i => $result) {
            $item = new WishlistItem(
                $result->kArtikel,
                $result->cArtikelName,
                $result->fAnzahl,
                $result->kWunschliste
            );

            $item->kWunschlistePos  = $result->kWunschlistePos;
            $item->cKommentar       = $result->cKommentar;
            $item->dHinzugefuegt    = $result->dHinzugefuegt;
            $item->dHinzugefuegt_de = $result->dHinzugefuegt_de;

            $wlPositionAttributes = $db->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*, teigenschaftsprache.cName
                    FROM twunschlisteposeigenschaft
                    JOIN teigenschaftsprache 
                        ON teigenschaftsprache.kEigenschaft = twunschlisteposeigenschaft.kEigenschaft
                    WHERE twunschlisteposeigenschaft.kWunschlistePos = :wlID
                    GROUP BY twunschlisteposeigenschaft.kWunschlistePosEigenschaft',
                ['wlID' => (int)$result->kWunschlistePos],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($wlPositionAttributes as $wlPositionAttribute) {
                if ($wlPositionAttribute->cFreifeldWert !== '') {
                    $wlPositionAttribute->cEigenschaftName     = $wlPositionAttribute->cName;
                    $wlPositionAttribute->cEigenschaftWertName = $wlPositionAttribute->cFreifeldWert;
                }
                $wlAttribute = new WishlistItemProperty(
                    $wlPositionAttribute->kEigenschaft,
                    $wlPositionAttribute->kEigenschaftWert,
                    $wlPositionAttribute->cFreifeldWert,
                    $wlPositionAttribute->cEigenschaftName,
                    $wlPositionAttribute->cEigenschaftWertName,
                    $wlPositionAttribute->kWunschlistePos
                );

                $wlAttribute->kWunschlistePosEigenschaft = $wlPositionAttribute->kWunschlistePosEigenschaft;

                $item->CWunschlistePosEigenschaft_arr[] = $wlAttribute;
            }

            $item->Artikel = new Artikel();
            try {
                $item->Artikel->fuelleArtikel($result->kArtikel, Artikel::getDefaultOptions());
            } catch (Exception $e) {
                continue;
            }
            $item->cArtikelName = $item->Artikel->cName;

            if (Frontend::getCustomerGroup()->isMerchant()) {
                $proce = (int)$item->fAnzahl * $item->Artikel->Preise->fVKNetto;
            } else {
                $proce = (int)$item->fAnzahl
                    * ($item->Artikel->Preise->fVKNetto
                       * (100 + $_SESSION['Steuersatz'][$item->Artikel->kSteuerklasse]) / 100);
            }

            $item->cPreis      = Preise::getLocalizedPriceString($proce, Frontend::getCurrency());
            $searchResults[$i] = $item;
        }

        return $searchResults;
    }

    /**
     * @param string $query
     * @return self
     * @since 5.0.0
     */
    public function filterPositions(string $query): self
    {
        $query = (string)Text::filterXSS($query);

        if ($query !== '') {
            $this->CWunschlistePos_arr = $this->sucheInWunschliste($query);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins               = new stdClass();
        $ins->kKunde       = $this->kKunde;
        $ins->cName        = $this->cName;
        $ins->nStandard    = $this->nStandard;
        $ins->nOeffentlich = $this->nOeffentlich;
        $ins->dErstellt    = $this->dErstellt;
        $ins->cURLID       = $this->cURLID;

        $this->kWunschliste = Shop::Container()->getDB()->insert('twunschliste', $ins);

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function ladeWunschliste(int $id = 0): self
    {
        if ($id <= 0) {
            $id = $this->kWunschliste;
        }
        if ($id <= 0) {
            return $this->reset();
        }

        $data = Shop::Container()->getDB()->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM twunschliste
                WHERE kWunschliste = :wlID",
            ['wlID' => $id],
            ReturnType::SINGLE_OBJECT
        );

        if (!$data || !$this->validate($data)) {
            return $this->reset();
        }

        return $this->setRecord($data);
    }

    /**
     * @return string
     */
    public function ueberpruefePositionen(): string
    {
        $names  = [];
        $notice = '';
        $db     = Shop::Container()->getDB();
        foreach ($this->CWunschlistePos_arr as $wlPosition) {
            if (!isset($wlPosition->kArtikel) || (int)$wlPosition->kArtikel <= 0) {
                continue;
            }
            $exists = $db->select('tartikel', 'kArtikel', $wlPosition->kArtikel);
            if (isset($exists->kArtikel) && (int)$exists->kArtikel > 0) {
                $visibility = $db->select(
                    'tartikelsichtbarkeit',
                    'kArtikel',
                    (int)$wlPosition->kArtikel,
                    'kKundengruppe',
                    Frontend::getCustomerGroup()->getID()
                );
                if ($visibility === null || empty($visibility->kArtikel)) {
                    if (\count($wlPosition->CWunschlistePosEigenschaft_arr) > 0) {
                        if (Product::isVariChild($wlPosition->kArtikel)) {
                            foreach ($wlPosition->CWunschlistePosEigenschaft_arr as $wlAttribute) {
                                $attrValExists = $db->select(
                                    'teigenschaftkombiwert',
                                    'kEigenschaftKombi',
                                    (int)$exists->kEigenschaftKombi,
                                    'kEigenschaftWert',
                                    (int)$wlAttribute->kEigenschaftWert,
                                    'kEigenschaft',
                                    (int)$wlAttribute->kEigenschaft,
                                    false,
                                    'kEigenschaftKombi'
                                );
                                if (empty($attrValExists->kEigenschaftKombi)) {
                                    $names[] = $wlPosition->cArtikelName;
                                    $notice .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                    $this->delWunschlistePosSess($wlPosition->kArtikel);
                                    break;
                                }
                            }
                        } else {
                            $attributes = $db->selectAll(
                                'teigenschaft',
                                'kArtikel',
                                (int)$wlPosition->kArtikel,
                                'kEigenschaft, cName, cTyp'
                            );
                            if (\count($attributes) > 0) {
                                foreach ($wlPosition->CWunschlistePosEigenschaft_arr as $wlAttribute) {
                                    $attrValExists = null;
                                    if (!empty($wlAttribute->kEigenschaft)) {
                                        $attrValExists = $db->select(
                                            'teigenschaftwert',
                                            'kEigenschaftWert',
                                            (int)$wlAttribute->kEigenschaftWert,
                                            'kEigenschaft',
                                            (int)$wlAttribute->kEigenschaft
                                        );
                                        if (empty($attrValExists)) {
                                            $attrValExists = $db->select(
                                                'twunschlisteposeigenschaft',
                                                'kEigenschaft',
                                                $wlAttribute->kEigenschaft
                                            );
                                        }
                                    }
                                    if (empty($attrValExists->kEigenschaftWert)
                                        && empty($attrValExists->cFreifeldWert)
                                    ) {
                                        $names[] = $wlPosition->cArtikelName;
                                        $notice .= '<br />' .
                                            Shop::Lang()->get('noProductWishlist', 'messages');

                                        $this->delWunschlistePosSess($wlPosition->kArtikel);
                                        break;
                                    }
                                }
                            } else {
                                $this->delWunschlistePosSess($wlPosition->kArtikel);
                            }
                        }
                    }
                } else {
                    $names[] = $wlPosition->cArtikelName;
                    $notice .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                    $this->delWunschlistePosSess($wlPosition->kArtikel);
                }
            } else {
                $names[] = $wlPosition->cArtikelName;
                $notice .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                $this->delWunschlistePosSess($wlPosition->kArtikel);
            }
        }

        $notice .= \implode(', ', $names);
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $notice, 'wlNote');

        return $notice;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public function delWunschlistePosSess(int $productID): bool
    {
        if (!$productID) {
            return false;
        }
        $db = Shop::Container()->getDB();
        foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $item) {
            if ($productID === (int)$item->kArtikel) {
                unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                \array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
                $db->delete(
                    'twunschlistepos',
                    'kWunschlistePos',
                    (int)$item->kWunschlistePos
                );
                $db->delete(
                    'twunschlisteposeigenschaft',
                    'kWunschlistePos',
                    (int)$item->kWunschlistePos
                );
                break;
            }
        }

        return true;
    }

    /**
     * @return $this
     */
    public function umgebungsWechsel(): self
    {
        if (\count($_SESSION['Wunschliste']->CWunschlistePos_arr) > 0) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $item) {
                $product = new Artikel();
                try {
                    $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                } catch (Exception $e) {
                    continue;
                }
                $_SESSION['Wunschliste']->CWunschlistePos_arr[$i]->Artikel      = $product;
                $_SESSION['Wunschliste']->CWunschlistePos_arr[$i]->cArtikelName = $product->cName;
            }
        }

        return $this;
    }

    /**
     * Überprüft Parameter und gibt falls erfolgreich kWunschliste zurück, ansonten 0
     *
     * @return int
     * @former checkeWunschlisteParameter()
     * @since  5.0.0
     */
    public static function checkeParameters(): int
    {
        $urlID = (string)Text::filterXSS(Request::verifyGPDataString('wlid'));

        if ($urlID !== '') {
            $campaign = new Campaign(\KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
            $id       = $campaign->kKampagne > 0
                ? ($urlID . '&' . $campaign->cParameter . '=' . $campaign->cWert)
                : $urlID;
            $keys     = ['nOeffentlich', 'cURLID'];
            $values   = [1, $id];
            $wishList = Shop::Container()->getDB()
                ->select('twunschliste', $keys, $values, null, null, null, null, false, 'kWunschliste');

            if ($wishList !== null && $wishList->kWunschliste > 0) {
                return (int)$wishList->kWunschliste;
            }
        }

        return 0;
    }

    /**
     * @return bool
     * @since  5.0.0
     */
    public function isSelfControlled(): bool
    {
        return $this->kKunde > 0 && $this->kKunde === Frontend::getCustomer()->getID();
    }

    /**
     * Holt für einen Kunden die aktive Wunschliste (falls vorhanden) aus der DB und fügt diese in die Session
     */
    public static function persistInSession(): void
    {
        if (!empty($_SESSION['Kunde']->kKunde)) {
            $data = Shop::Container()->getDB()->select(
                'twunschliste',
                ['kKunde', 'nStandard'],
                [(int)$_SESSION['Kunde']->kKunde, 1]
            );
            if (isset($data->kWunschliste)) {
                $_SESSION['Wunschliste'] = new Wishlist((int)$data->kWunschliste);
                $_SESSION['Wunschliste']->ueberpruefePositionen();
            }
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public static function delete(int $id): string
    {
        $msg = '';
        if ($id === 0) {
            return $msg;
        }
        $db       = Shop::Container()->getDB();
        $data     = $db->select('twunschliste', 'kWunschliste', $id);
        $customer = Frontend::getCustomer();
        if (isset($data->kKunde) && (int)$data->kKunde === $customer->getID()) {
            $items = $db->selectAll(
                'twunschlistepos',
                'kWunschliste',
                $id,
                'kWunschlistePos'
            );
            foreach ($items as $item) {
                $db->delete(
                    'twunschlisteposeigenschaft',
                    'kWunschlistePos',
                    (int)$item->kWunschlistePos
                );
            }
            $db->delete('twunschlistepos', 'kWunschliste', $id);
            $db->delete('twunschliste', 'kWunschliste', $id);
            if (isset($_SESSION['Wunschliste']->kWunschliste) && (int)$_SESSION['Wunschliste']->kWunschliste === $id) {
                unset($_SESSION['Wunschliste']);
            }
            // Wenn die gelöschte Wunschliste nStandard = 1 war => neue setzen
            if ((int)$data->nStandard === 1) {
                // Neue Wunschliste holen (falls vorhanden) und nStandard=1 neu setzen
                $data = $db->select('twunschliste', 'kKunde', $customer->getID());
                if (isset($data->kWunschliste)) {
                    $db->query(
                        'UPDATE twunschliste
                            SET nStandard = 1
                            WHERE kWunschliste = ' . (int)$data->kWunschliste,
                        ReturnType::AFFECTED_ROWS
                    );
                    // Neue Standard Wunschliste in die Session laden
                    $_SESSION['Wunschliste'] = new Wishlist((int)$data->kWunschliste);
                    $_SESSION['Wunschliste']->ueberpruefePositionen();
                }
            }
            $msg = Shop::Lang()->get('wishlistDelete', 'messages');
        }

        return $msg;
    }

    /**
     * @param int $id
     * @param array|null $post
     * @return string
     */
    public static function update(int $id, array $post = null): string
    {
        $post = $post ?? $_POST;
        $db   = Shop::Container()->getDB();
        foreach (['wishlistName', 'WunschlisteName'] as $wishlistName) {
            if (Request::postVar($wishlistName, '') !== '') {
                $name = Text::htmlentities(Text::filterXSS(\mb_substr($post[$wishlistName], 0, 254)));
                $db->update('twunschliste', 'kWunschliste', $id, (object)['cName' => $name]);
            }
        }
        $items = $db->selectAll(
            'twunschlistepos',
            'kWunschliste',
            $id,
            'kWunschlistePos'
        );
        // Prüfen ab Positionen vorhanden
        if (\count($items) === 0) {
            return '';
        }
        foreach ($items as $item) {
            $id  = (int)$item->kWunschlistePos;
            $idx = 'Kommentar_' . $id;
            if (isset($post[$idx])) {
                $upd             = new stdClass();
                $upd->cKommentar = Text::htmlentities(Text::filterXSS($db->escape(\mb_substr($post[$idx], 0, 254))));
                $db->update('twunschlistepos', 'kWunschlistePos', $id, $upd);
            }

            $idx = 'Anzahl_' . $id;
            if (isset($post[$idx])) {
                $quantity = \str_replace(',', '.', $post[$idx]);
                if ((float)$quantity > 0) {
                    $db->update(
                        'twunschlistepos',
                        'kWunschlistePos',
                        $id,
                        (object)['fAnzahl' => (float)$quantity]
                    );
                }
            }
        }

        return Shop::Lang()->get('wishlistUpdate', 'messages');
    }

    /**
     * @param int $id
     * @return string
     */
    public static function setDefault(int $id): string
    {
        $msg = '';
        if ($id === 0) {
            return $msg;
        }
        // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
        $data = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $id);
        if ($data !== null && (int)$data->kKunde === Frontend::getCustomer()->getID()) {
            // Wunschliste auf Standard setzen
            Shop::Container()->getDB()->update(
                'twunschliste',
                'kKunde',
                (int)$_SESSION['Kunde']->kKunde,
                (object)['nStandard' => 0]
            );
            Shop::Container()->getDB()->update(
                'twunschliste',
                'kWunschliste',
                $id,
                (object)['nStandard' => 1]
            );
            unset($_SESSION['Wunschliste']);
            $_SESSION['Wunschliste'] = new Wishlist($id);
            $_SESSION['Wunschliste']->ueberpruefePositionen();

            $msg = Shop::Lang()->get('wishlistStandard', 'messages');
        }

        return $msg;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function save(string $name): string
    {
        $msg = '';
        if ($_SESSION['Kunde']->kKunde > 0 && !empty($name)) {
            $list            = new Wishlist();
            $list->cName     = $name;
            $list->nStandard = 0;
            unset(
                $list->CWunschlistePos_arr,
                $list->oKunde,
                $list->kWunschliste,
                $list->dErstellt_DE
            );

            Shop::Container()->getDB()->insert('twunschliste', $list);

            $msg = Shop::Lang()->get('wishlistAdd', 'messages');
        }

        return $msg;
    }

    /**
     * @param array $recipients
     * @param int   $id
     * @return string
     */
    public static function send(array $recipients, int $id): string
    {
        if (\count($recipients) === 0) {
            return Shop::Lang()->get('noEmail', 'messages');
        }
        $msg                        = '';
        $conf                       = Shop::getSettings([\CONF_GLOBAL]);
        $data                       = new stdClass();
        $data->tkunde               = $_SESSION['Kunde'];
        $data->twunschliste         = self::buildPrice(new Wishlist($id));
        $history                    = new stdClass();
        $history->kWunschliste      = $id;
        $history->dZeit             = 'NOW()';
        $history->nAnzahlEmpfaenger = \min(
            \count($recipients),
            (int)$conf['global']['global_wunschliste_max_email']
        );
        $history->nAnzahlArtikel    = \count($data->twunschliste->CWunschlistePos_arr);
        Shop::Container()->getDB()->insert('twunschlisteversand', $history);
        $validEmails = [];
        for ($i = 0; $i < $history->nAnzahlEmpfaenger; $i++) {
            // Email auf "Echtheit" prüfen
            $address = Text::filterXSS($recipients[$i]);
            if (!SimpleMail::checkBlacklist($address)) {
                $data->mail          = new stdClass();
                $data->mail->toEmail = $address;
                $data->mail->toName  = $address;

                $mailer = Shop::Container()->get(Mailer::class);
                $mail   = new Mail();
                $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_WUNSCHLISTE, $data));
            } else {
                $validEmails[] = $address;
            }
        }
        // Gab es Emails die nicht validiert wurden?
        if (\count($validEmails) > 0) {
            $msg = Shop::Lang()->get('novalidEmail', 'messages') . \implode(', ', $validEmails) . '<br />';
        }
        // Hat der Benutzer mehr Emails angegeben als erlaubt sind?
        if (\count($recipients) > (int)$conf['global']['global_wunschliste_max_email']) {
            $max  = \count($recipients) - (int)$conf['global']['global_wunschliste_max_email'];
            $msg .= '<br />';
            if (\mb_strpos($msg, Shop::Lang()->get('novalidEmail', 'messages')) === false) {
                $msg = Shop::Lang()->get('novalidEmail', 'messages');
            }

            for ($i = 0; $i < $max; $i++) {
                if (\mb_strpos($msg, $recipients[(\count($recipients) - 1) - $i]) === false) {
                    if ($i > 0) {
                        $msg .= ', ' . $recipients[(\count($recipients) - 1) - $i];
                    } else {
                        $msg .= $recipients[(\count($recipients) - 1) - $i];
                    }
                }
            }

            $msg .= '<br />';
        }
        $msg .= Shop::Lang()->get('emailSeccessfullySend', 'messages');

        return $msg;
    }

    /**
     * @param int $wishListID
     * @param int $itemID
     * @return array|bool
     */
    public static function getAttributesByID(int $wishListID, int $itemID)
    {
        if ($wishListID > 0 && $itemID > 0) {
            $data       = [];
            $attributes = Shop::Container()->getDB()->selectAll(
                'twunschlisteposeigenschaft',
                'kWunschlistePos',
                $itemID
            );
            foreach ($attributes as $attribute) {
                $value                       = new stdClass();
                $value->kEigenschaftWert     = $attribute->kEigenschaftWert;
                $value->kEigenschaft         = $attribute->kEigenschaft;
                $value->cEigenschaftName     = $attribute->cEigenschaftName;
                $value->cEigenschaftWertName = $attribute->cEigenschaftWertName;
                $value->cFreifeldWert        = $attribute->cFreifeldWert;

                $data[] = $value;
            }

            return $data;
        }

        return false;
    }

    /**
     * @param int $itemID
     * @return object|bool
     */
    public static function getWishListPositionDataByID(int $itemID)
    {
        if ($itemID <= 0) {
            return false;
        }
        $item = Shop::Container()->getDB()->select('twunschlistepos', 'kWunschlistePos', $itemID);
        if ($item === null) {
            return false;
        }
        $item->kWunschlistePos = (int)$item->kWunschlistePos;
        $item->kWunschliste    = (int)$item->kWunschliste;
        $item->kArtikel        = (int)$item->kArtikel;
        $product               = new Artikel();
        try {
            $product->fuelleArtikel($item->kArtikel, Artikel::getDefaultOptions());
        } catch (Exception $e) {
            return false;
        }
        if ($product->kArtikel > 0) {
            $item->bKonfig = $product->bHasKonfig;
        }

        return $item;
    }

    /**
     * @param int    $id
     * @param string $cURLID
     * @return bool|stdClass
     */
    public static function getWishListDataByID(int $id = 0, string $cURLID = '')
    {
        $wishlist = null;
        if ($id > 0) {
            $wishlist = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $id);
        } elseif ($cURLID !== '') {
            $wishlist = Shop::Container()->getDB()->queryPrepared(
                'SELECT * FROM twunschliste WHERE cURLID LIKE :id',
                ['id' => $cURLID],
                ReturnType::SINGLE_OBJECT
            );
        }
        if (isset($wishlist->kWunschliste) && $wishlist->kWunschliste > 0) {
            $wishlist->kWunschliste = (int)$wishlist->kWunschliste;
            $wishlist->kKunde       = (int)$wishlist->kKunde;
            $wishlist->nStandard    = (int)$wishlist->nStandard;
            $wishlist->nOeffentlich = (int)$wishlist->nOeffentlich;

            return $wishlist;
        }

        return false;
    }

    /**
     * @param Wishlist $wishList
     * @return Wishlist
     */
    public static function buildPrice(Wishlist $wishList): Wishlist
    {
        // Wunschliste durchlaufen und cPreis setzen (Artikelanzahl mit eingerechnet)
        if (\is_array($wishList->CWunschlistePos_arr) && \count($wishList->CWunschlistePos_arr) > 0) {
            foreach ($wishList->CWunschlistePos_arr as $item) {
                if (Frontend::getCustomerGroup()->isMerchant()) {
                    $price = isset($item->Artikel->Preise->fVKNetto)
                        ? (int)$item->fAnzahl * $item->Artikel->Preise->fVKNetto
                        : 0;
                } else {
                    $price = isset($item->Artikel->Preise->fVKNetto)
                        ? (int)$item->fAnzahl *
                        (
                            $item->Artikel->Preise->fVKNetto *
                            (100 + $_SESSION['Steuersatz'][$item->Artikel->kSteuerklasse]) / 100
                        )
                        : 0;
                }
                $item->cPreis = Preise::getLocalizedPriceString($price, Frontend::getCurrency());
            }
        }

        return $wishList;
    }

    /**
     * @param int $code
     * @return string
     */
    public static function mapMessage(int $code): string
    {
        switch ($code) {
            case 1:
                return Shop::Lang()->get('basketAdded', 'messages');
            case 2:
                return Shop::Lang()->get('basketAllAdded', 'messages');
            default:
                return '';
        }
    }

    /**
     * @param object|null $record
     * @return self
     * @since 5.0.0
     */
    public function setRecord(?object $record): self
    {
        if ($record === null || !$this->validate($record)) {
            return $this->reset();
        }

        $this->kWunschliste = (int)$record->kWunschliste;
        $this->kKunde       = (int)$record->kKunde;
        $this->nStandard    = (int)$record->nStandard;
        $this->nOeffentlich = (int)$record->nOeffentlich;
        $this->cName        = $record->cName;
        $this->cURLID       = $record->cURLID;
        $this->dErstellt    = $record->dErstellt;
        $this->dErstellt_DE = $record->dErstellt_DE ?? \DateTime::createFromFormat('Y-m-d H:i:s', $record->dErstellt)
                ->format('d.m.Y H:i');
        if ($this->kKunde > 0) {
            $this->oKunde            = new Customer($this->kKunde);
            $this->oKunde->cPasswort = null;
            $this->oKunde->fRabatt   = null;
            $this->oKunde->fGuthaben = null;
            $this->oKunde->cUSTID    = null;
        }
        $db             = Shop::Container()->getDB();
        $langID         = Shop::getLanguageID();
        $positions      = $db->selectAll(
            'twunschlistepos',
            'kWunschliste',
            $this->kWunschliste,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de'
        );
        $defaultOptions = Artikel::getDefaultOptions();
        // Hole alle Eigenschaften für eine Position
        foreach ($positions as $position) {
            $position->kWunschlistePos = (int)$position->kWunschlistePos;
            $position->kWunschliste    = (int)$position->kWunschliste;
            $position->kArtikel        = (int)$position->kArtikel;

            $wlPosition = new WishlistItem(
                $position->kArtikel,
                $position->cArtikelName,
                $position->fAnzahl,
                $position->kWunschliste
            );

            $wlPosition->kWunschlistePos  = $position->kWunschlistePos;
            $wlPosition->cKommentar       = $position->cKommentar;
            $wlPosition->dHinzugefuegt    = $position->dHinzugefuegt;
            $wlPosition->dHinzugefuegt_de = $position->dHinzugefuegt_de;

            $wlPositionAttributes = $db->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*,
                    IF(LENGTH(teigenschaftsprache.cName) > 0,
                        teigenschaftsprache.cName,
                        twunschlisteposeigenschaft.cEigenschaftName) AS cName,
                    IF(LENGTH(teigenschaftwertsprache.cName) > 0,
                        teigenschaftwertsprache.cName,
                        twunschlisteposeigenschaft.cEigenschaftWertName) AS cWert
                    FROM twunschlisteposeigenschaft
                    LEFT JOIN teigenschaftsprache
                        ON teigenschaftsprache.kEigenschaft = twunschlisteposeigenschaft.kEigenschaft
                        AND teigenschaftsprache.kSprache = :langID
                    LEFT JOIN teigenschaftwertsprache
                        ON teigenschaftwertsprache.kEigenschaftWert = twunschlisteposeigenschaft.kEigenschaftWert
                        AND teigenschaftwertsprache.kSprache = :langID
                    WHERE twunschlisteposeigenschaft.kWunschlistePos = :wlID
                    GROUP BY twunschlisteposeigenschaft.kWunschlistePosEigenschaft',
                [
                    'langID' => $langID,
                    'wlID'   => $position->kWunschlistePos
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($wlPositionAttributes as $wlPositionAttribute) {
                if ($wlPositionAttribute->cFreifeldWert !== '') {
                    if (empty($wlPositionAttribute->cName)) {
                        $_cName                     = $db->queryPrepared(
                            'SELECT IF(LENGTH(teigenschaftsprache.cName) > 0,
                                teigenschaftsprache.cName,
                                teigenschaft.cName) AS cName
                                FROM teigenschaft
                                LEFT JOIN teigenschaftsprache
                                    ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                    AND teigenschaftsprache.kSprache = :langID
                                WHERE teigenschaft.kEigenschaft = :attrID',
                            [
                                'langID' => $langID,
                                'attrID' => (int)$wlPositionAttribute->kEigenschaft
                            ],
                            ReturnType::SINGLE_OBJECT
                        );
                        $wlPositionAttribute->cName = $_cName->cName;
                    }
                    $wlPositionAttribute->cWert = $wlPositionAttribute->cFreifeldWert;
                }

                $wlAttribute = new WishlistItemProperty(
                    $wlPositionAttribute->kEigenschaft,
                    $wlPositionAttribute->kEigenschaftWert,
                    $wlPositionAttribute->cFreifeldWert,
                    $wlPositionAttribute->cName,
                    $wlPositionAttribute->cWert,
                    $wlPositionAttribute->kWunschlistePos
                );

                $wlAttribute->kWunschlistePosEigenschaft      = (int)$wlPositionAttribute->kWunschlistePosEigenschaft;
                $wlPosition->CWunschlistePosEigenschaft_arr[] = $wlAttribute;
            }
            $wlPosition->Artikel = new Artikel();
            try {
                $wlPosition->Artikel->fuelleArtikel($wlPosition->kArtikel, $defaultOptions);
            } catch (Exception $e) {
                continue;
            }
            $wlPosition->cArtikelName    = $wlPosition->Artikel->cName === ''
                ? $wlPosition->cArtikelName
                : $wlPosition->Artikel->cName;
            $this->CWunschlistePos_arr[] = $wlPosition;
        }

        return $this;
    }

    /**
     * @param int $id
     */
    public static function setPrivate(int $id): void
    {
        $upd               = new stdClass();
        $upd->nOeffentlich = 0;
        $upd->cURLID       = '';
        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $id, $upd);
    }

    /**
     * @param int $id
     */
    public static function setPublic(int $id): void
    {
        $urlID    = \uniqid('', true);
        $campaign = new Campaign(\KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
        if ($campaign->kKampagne > 0) {
            $urlID .= '&' . $campaign->cParameter . '=' . $campaign->cWert;
        }
        $upd               = new stdClass();
        $upd->nOeffentlich = 1;
        $upd->cURLID       = $urlID;
        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $id, $upd);
    }

    /**
     * @return Collection
     */
    public static function getWishlists(): Collection
    {
        $customerID = Frontend::getCustomer()->getID();
        if ($customerID === 0) {
            return new Collection();
        }

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tw.*, COUNT(twp.kArtikel) AS productCount
                FROM twunschliste AS tw
                    LEFT JOIN twunschlistepos AS twp USING (kWunschliste)
                WHERE kKunde = :customerID
                GROUP BY tw.kWunschliste
                ORDER BY tw.nStandard DESC',
            ['customerID' => $customerID],
            ReturnType::COLLECTION
        )->map(static function ($list) {
            $list->kWunschliste = (int)$list->kWunschliste;
            $list->kKunde       = (int)$list->kKunde;
            $list->nStandard    = (int)$list->nStandard;
            $list->nOeffentlich = (int)$list->nOeffentlich;
            $list->productCount = (int)$list->productCount;

            return $list;
        });
    }

    /**
     * @param int $product
     * @param array $params
     * @return int
     */
    public static function checkVariOnList(int $product, array $params): int
    {
        $variationCount = \count($params);
        $wishlist       = Frontend::getWishList();
        foreach ($wishlist->CWunschlistePos_arr as $item) {
            if ($product === $item->kArtikel) {
                $variCountTMP = 0;
                foreach ($item->CWunschlistePosEigenschaft_arr as $itemAttribute) {
                    if (isset($params[$itemAttribute->kEigenschaft])
                        && ((string)$itemAttribute->kEigenschaftWert === $params[$itemAttribute->kEigenschaft]
                            || (string)$itemAttribute->cFreifeldWert === $params[$itemAttribute->kEigenschaft])
                    ) {
                        $variCountTMP++;
                    }
                }
                if ($variCountTMP === $variationCount) {
                    return $item->kWunschlistePos;
                }
            }
        }

        return 0;
    }
}
