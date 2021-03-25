<?php

use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Checkout\Kupon;
use JTL\Checkout\Versandart;
use JTL\Checkout\Zahlungsart;
use JTL\Customer\Customer;
use JTL\Filter\Metadata;
use JTL\Filter\ProductFilter;
use JTL\GeneralDataProtection\IpAnonymizer;
use JTL\Helpers\Category;
use JTL\Helpers\Date;
use JTL\Helpers\FileSystem;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\PHPSettings;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\SearchSpecial;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Jtllog;
use JTL\Language\LanguageHelper;
use JTL\Link\LinkGroupCollection;
use JTL\Redirect;
use JTL\Services\JTL\LinkService;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Visitor;

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cClass
 * @param bool   $bForceSteuer
 * @return string
 * @deprecated since 5.0.0
 */
function getCurrencyConversion($fPreisNetto, $fPreisBrutto, $cClass = '', bool $bForceSteuer = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Currency::class . '::getCurrencyConversion() instead',
        E_USER_DEPRECATED
    );
    return Currency::getCurrencyConversion($fPreisNetto, $fPreisBrutto, $cClass, $bForceSteuer);
}

/**
 * @param string $data
 * @return int
 * @deprecated since 5.0.0
 */
function checkeTel($data)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Text::class . '::checkPhoneNumber instead',
        E_USER_DEPRECATED
    );
    return Text::checkPhoneNumber($data);
}

/**
 * @param string $data
 * @return int
 * @deprecated since 5.0.0
 */
function checkeDatum($data)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Text::class . '::checkDate instead', E_USER_DEPRECATED);
    return Text::checkDate($data);
}

/**
 * @param string      $cPasswort
 * @param null|string $cHashPasswort
 * @return bool|string
 * @deprecated since 5.0.0
 */
function cryptPasswort($cPasswort, $cHashPasswort = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

    $cSalt   = sha1(uniqid(mt_rand(), true));
    $nLaenge = mb_strlen($cSalt);
    $nLaenge = max($nLaenge >> 3, ($nLaenge >> 2) - mb_strlen($cPasswort));
    $cSalt   = $cHashPasswort
        ? mb_substr($cHashPasswort, min(mb_strlen($cPasswort), mb_strlen($cHashPasswort) - $nLaenge), $nLaenge)
        : strrev(mb_substr($cSalt, 0, $nLaenge));
    $cHash   = sha1($cPasswort);
    $cHash   = sha1(mb_substr($cHash, 0, mb_strlen($cPasswort)) . $cSalt . mb_substr($cHash, mb_strlen($cPasswort)));
    $cHash   = mb_substr($cHash, $nLaenge);
    $cHash   = mb_substr($cHash, 0, mb_strlen($cPasswort)) . $cSalt . mb_substr($cHash, mb_strlen($cPasswort));

    return $cHashPasswort && $cHashPasswort !== $cHash ? false : $cHash;
}

/**
 * @param int    $length
 * @param string $seed
 * @return bool|string
 * @deprecated since 5.0.0
 */
function gibUID(int $length = 40, string $seed = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $uid      = '';
    $salt     = '';
    $saltBase = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
    // Gen SALT
    for ($j = 0; $j < 30; $j++) {
        $salt .= mb_substr($saltBase, mt_rand(0, mb_strlen($saltBase) - 1), 1);
    }
    $salt = md5($salt);
    mt_srand();
    // Wurde ein String übergeben?
    if (mb_strlen($seed) > 0) {
        // Hat der String Elemente?
        [$strings] = explode(';', $seed);
        if (is_array($strings) && count($strings) > 0) {
            foreach ($strings as $string) {
                $uid .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
            }

            $uid = md5($uid . $salt);
        } else {
            $sl = mb_strlen($seed);
            for ($i = 0; $i < $sl; $i++) {
                $pos = mt_rand(0, mb_strlen($seed) - 1);
                if (((int)date('w') % 2) <= mb_strlen($seed)) {
                    $pos = (int)date('w') % 2;
                }
                $uid .= md5(mb_substr($seed, $pos, 1) . $salt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
            }
        }
        $uid = cryptPasswort($uid . $salt);
    } else {
        $uid = cryptPasswort(md5(M_PI . $salt . md5(time() - mt_rand())));
    }
    // Anzahl Stellen beachten
    return $length > 0 ? mb_substr($uid, 0, $length) : $uid;
}

/**
 * @param float $sum
 * @return float
 * @deprecated since 5.0.0 - use \JTL\Cart\CartHelper::roundOptional instead
 */
function optionaleRundung($sum)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::roundOptional() instead',
        E_USER_DEPRECATED
    );
    return CartHelper::roundOptional($sum);
}

/**
 * @deprecated since 4.0
 * @return int
 */
function gibSeitenTyp()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getPageType();
}

/**
 * @deprecated since 4.0
 * @param string $string
 * @param int    $search
 * @return mixed|string
 */
function filterXSS($string, $search = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Text::filterXSS($string, $search);
}

/**
 * @deprecated since 4.0
 * @param bool $forceSSL
 * @return string
 */
function gibShopURL($forceSSL = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getURL($forceSSL);
}

/**
 * @deprecated since 4.0 - use Jtllog::writeLog() insted
 * @param string $logfile
 * @param string $entry
 * @param int    $level
 * @return bool
 */
function writeLog($logfile, $entry, $level)
{
    if (ES_LOGGING > 0 && ES_LOGGING >= $level) {
        $logfile = fopen($logfile, 'a');
        if (!$logfile) {
            return false;
        }
        fwrite(
            $logfile,
            "\n[" . date('m.d.y H:i:s') . '] ' .
            '[' . (new IpAnonymizer(Request::getRealIP()))->anonymize() . "]\n" .
            $entry
        );
        fclose($logfile);
    }

    return true;
}

/**
 * https? wenn erwünscht reload mit https
 *
 * @return bool
 * @deprecated since 4.06
 */
function pruefeHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 4.06
 */
function loeseHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holePreisanzeigeEinstellungen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @deprecated since 5.0.0
 */
function checkeWarenkorbEingang()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::checkAdditions() instead.',
        E_USER_DEPRECATED
    );
    CartHelper::checkAdditions();
}

/**
 * @param Artikel|object $product
 * @param int            $qty
 * @param array          $attributeValues
 * @param int            $precision
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeFuegeEinInWarenkorb($product, $qty, $attributeValues, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::addToCartCheck($product, $qty, $attributeValues, $precision);
}

/**
 * @param string         $lieferland
 * @param string         $versandklassen
 * @param int            $customergGroupID
 * @param Artikel|object $product
 * @param bool           $checkDepedency
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibGuenstigsteVersandart($lieferland, $versandklassen, $customergGroupID, $product, $checkDepedency = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return ShippingMethod::getFavourableShippingMethod(
        $lieferland,
        $versandklassen,
        $customergGroupID,
        $product,
        $checkDepedency
    );
}

/**
 * Gibt von einem Artikel mit normalen Variationen, ein Array aller ausverkauften Variationen zurück
 *
 * @param int          $productID
 * @param null|Artikel $product
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function pruefeVariationAusverkauft(int $productID = 0, $product = null): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($productID > 0) {
        $options               = Artikel::getDefaultOptions();
        $options->nVariationen = 1;
        $product               = (new Artikel())->fuelleArtikel($productID, $options);
    }

    $soldOut = [];
    if ($product !== null
        && (int)$product->kArtikel > 0
        && $product->kEigenschaftKombi === 0
        && $product->nIstVater === 0
        && $product->Variationen !== null
        && count($product->Variationen) > 0
    ) {
        foreach ($product->Variationen as $oVariation) {
            if (!isset($oVariation->Werte) || count($oVariation->Werte) === 0) {
                continue;
            }
            foreach ($oVariation->Werte as $oVariationWert) {
                // Ist Variation ausverkauft?
                if ($oVariationWert->fLagerbestand <= 0) {
                    $oVariationWert->cNameEigenschaft   = $oVariation->cName;
                    $soldOut[$oVariation->kEigenschaft] = $oVariationWert;
                }
            }
        }
    }

    return $soldOut;
}

/**
 * Sortiert ein Array von Objekten anhand von einem bestimmten Member vom Objekt
 * z.B. sortiereFilter($NaviFilter->MerkmalFilter, "kMerkmalWert");
 *
 * @param array $filters
 * @param string $keyName
 * @return array
 * @deprecated since 5.0.0 - not used in core
 */
function sortiereFilter($filters, $keyName)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $keys   = [];
    $sorted = [];
    if (is_array($filters) && count($filters) > 0) {
        foreach ($filters as $filter) {
            // Baue das Array mit Keys auf, die sortiert werden sollen
            $keys[] = (int)$filter->$keyName;
        }
        // Sortiere das Array
        sort($keys, SORT_NUMERIC);
        foreach ($keys as $key) {
            foreach ($filters as $filter) {
                if ((int)$filter->$keyName === $key) {
                    // Baue das Array auf, welches sortiert zurueckgegeben wird
                    $sorted[] = $filter;
                    break;
                }
            }
        }
    }

    return $sorted;
}

/**
 * Holt die Globalen Metaangaben und Return diese als Assoc Array wobei die Keys => kSprache sind
 *
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function holeGlobaleMetaAngaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Metadata::getGlobalMetaData();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeExcludedKeywords()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Metadata::getExcludes();
}

/**
 * Erhält einen String aus dem alle nicht erlaubten Wörter rausgefiltert werden
 *
 * @param string $string
 * @param array  $excludes
 * @return string
 * @deprecated since 5.0.0
 */
function gibExcludesKeywordsReplace($string, $excludes)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_array($excludes) && count($excludes) > 0) {
        foreach ($excludes as $i => $oExcludesKeywords) {
            $excludes[$i] = ' ' . $oExcludesKeywords . ' ';
        }

        return str_replace($excludes, ' ', $string);
    }

    return $string;
}

/**
 * @param float $sum
 * @return string
 * @deprecated since 5.0.0 - not used in core
 */
function formatCurrency($sum)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sum    = (float)$sum;
    $sumAbs = null;
    $cents  = null;
    if ($sum > 0) {
        $sumAbs = abs($sum);
        $sum    = floor($sum * 100);
        $cents  = $sum % 100;
        $sum    = (string)floor($sum / 100);
        if ($cents < 10) {
            $cents = '0' . $cents;
        }
        for ($i = 0; $i < floor((mb_strlen($sum) - (1 + $i)) / 3); $i++) {
            $sum = mb_substr($sum, 0, mb_strlen($sum) - (4 * $i + 3)) . '.' .
                mb_substr($sum, 0, mb_strlen($sum) - (4 * $i + 3));
        }
    }

    return (($sumAbs ? '' : '-') . $sum . ',' . $cents);
}

/**
 * Mapped die Suchspecial Einstellungen und liefert die Einstellungswerte als Assoc Array zurück.
 * Das Array kann via kKey Assoc angesprochen werden.
 *
 * @param array $config
 * @return array
 * @deprecated since 5.0.0
 */
function gibSuchspecialEinstellungMapping(array $config): array
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $assoc = [];
    foreach ($config as $key => $oSuchspecialEinstellung) {
        switch ($key) {
            case 'suchspecials_sortierung_bestseller':
                $assoc[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_sonderangebote':
                $assoc[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_neuimsortiment':
                $assoc[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topangebote':
                $assoc[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_inkuerzeverfuegbar':
                $assoc[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topbewertet':
                $assoc[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                break;
        }
    }

    return $assoc;
}

/**
 * @param int $pageType
 * @return string
 * @deprecated since 5.0.0 - not used in core
 */
function mappeSeitentyp(int $pageType)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    switch ($pageType) {
        case PAGE_ARTIKEL:
            return 'Artikeldetails';

        case PAGE_ARTIKELLISTE:
            return 'ArtikelListe';

        case PAGE_WARENKORB:
            return 'Warenkorb';

        case PAGE_MEINKONTO:
            return 'Mein Konto';

        case PAGE_KONTAKT:
            return 'Kontakt';

        case PAGE_NEWS:
            return 'News';

        case PAGE_NEWSLETTER:
            return 'Newsletter';

        case PAGE_LOGIN:
            return 'Login';

        case PAGE_REGISTRIERUNG:
            return 'Registrierung';

        case PAGE_BESTELLVORGANG:
            return 'Bestellvorgang';

        case PAGE_BEWERTUNG:
            return 'Bewertung';

        case PAGE_PASSWORTVERGESSEN:
            return 'Passwort vergessen';

        case PAGE_WARTUNG:
            return 'Wartung';

        case PAGE_WUNSCHLISTE:
            return 'Wunschliste';

        case PAGE_VERGLEICHSLISTE:
            return 'Vergleichsliste';

        case PAGE_STARTSEITE:
            return 'Startseite';

        case PAGE_VERSAND:
            return 'Versand';

        case PAGE_AGB:
            return 'AGB';

        case PAGE_DATENSCHUTZ:
            return 'Datenschutz';

        case PAGE_LIVESUCHE:
            return 'Livesuche';

        case PAGE_HERSTELLER:
            return 'Hersteller';

        case PAGE_SITEMAP:
            return 'Sitemap';

        case PAGE_GRATISGESCHENK:
            return 'Gratis Geschenk ';

        case PAGE_WRB:
            return 'WRB';

        case PAGE_PLUGIN:
            return 'Plugin';

        case PAGE_NEWSLETTERARCHIV:
            return 'Newsletterarchiv';

        case PAGE_EIGENE:
            return 'Eigene Seite';

        case PAGE_UNBEKANNT:
        default:
            return 'Unbekannt';
    }
}

/**
 * @param bool $cache
 * @return int
 * @deprecated since 5.0.0
 */
function getSytemlogFlag($cache = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Jtllog::getSytemlogFlag($cache);
}

/**
 * @deprecated since 5.0.0
 */
function baueKategorieListenHTML()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Smarty()->assign('cKategorielistenHTML_arr', []);
}

/**
 * @param Kategorie $currentCategory
 * @deprecated since 5.0
 */
function baueUnterkategorieListeHTML($currentCategory)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Smarty()->assign('oUnterKategorien_arr', Category::getSubcategoryList($currentCategory->kKategorie));
}

/**
 * @param Kategorie $category
 * @param int       $customergGroupID
 * @param int       $languageID
 * @param bool      $asString
 * @return array|string
 * @deprecated since 5.0.0
 */
function gibKategoriepfad($category, $customergGroupID, $languageID, $asString = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Category::getInstance($languageID, $customergGroupID)->getPath($category, $asString);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibLagerfilter()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
}

/**
 * @param array $variBoxAnzahl_arr
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeVariBoxAnzahl($variBoxAnzahl_arr = [])
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CartHelper::checkVariboxAmount($variBoxAnzahl_arr);
}

/**
 * @param string $path
 * @return string
 * @deprecated since 5.0.0 - not used in core anymore
 */
function gibArtikelBildPfad($path)
{
    return mb_strlen(trim($path)) > 0
        ? $path
        : BILD_KEIN_ARTIKELBILD_VORHANDEN;
}

/**
 * @param int $categoryBoxID
 * @return array
 * @deprecated since 5.0.0 - not used in core anymore
 */
function gibAlleKategorienNoHTML($categoryBoxID = 0)
{
    $categories = [];
    $depth      = 0;
    if (K_KATEGORIE_TIEFE <= 0) {
        return $categories;
    }
    $categoryList = new KategorieListe();
    $categoryList->getAllCategoriesOnLevel(0);
    foreach ($categoryList->elemente as $category) {
        $catID = $category->kKategorie;
        //Kategoriebox Filter
        if ($categoryBoxID > 0
            && $depth === 0
            && $category->CategoryFunctionAttributes[KAT_ATTRIBUT_KATEGORIEBOX] != $categoryBoxID
        ) {
            continue;
        }
        unset($categoriesNoHTML);
        $categoriesNoHTML = $category;
        unset($categoriesNoHTML->Unterkategorien);
        $categoriesNoHTML->oUnterKat_arr = [];
        $categories[$catID]              = $categoriesNoHTML;
        //nur wenn unterkategorien enthalten sind!
        if (K_KATEGORIE_TIEFE < 2) {
            continue;
        }
        $currentCat = new Kategorie($catID);
        if ($currentCat->bUnterKategorien) {
            $depth         = 1;
            $subCategories = new KategorieListe();
            $subCategories->getAllCategoriesOnLevel($currentCat->kKategorie);
            foreach ($subCategories->elemente as $subCat) {
                $subID = (int)$subCat->kKategorie;
                unset($categoriesNoHTML);
                $categoriesNoHTML = $subCat;
                unset($categoriesNoHTML->Unterkategorien);
                $categoriesNoHTML->oUnterKat_arr           = [];
                $categories[$catID]->oUnterKat_arr[$subID] = $categoriesNoHTML;

                if (K_KATEGORIE_TIEFE < 3) {
                    continue;
                }
                $depth            = 2;
                $subSubCategories = new KategorieListe();
                $subSubCategories->getAllCategoriesOnLevel($subID);
                foreach ($subSubCategories->elemente as $subSubCat) {
                    $subSubID = $subSubCat->kKategorie;
                    unset($categoriesNoHTML);
                    $categoriesNoHTML = $subSubCat;
                    unset($categoriesNoHTML->Unterkategorien);
                    $categories[$catID]->oUnterKat_arr[$subID]->oUnterKat_arr[$subSubID] = $categoriesNoHTML;
                }
            }
        }
    }

    return $categories;
}

/**
 * @return null
 * @deprecated since 4.06.10 - should not be used anymore; is replaced by SHOP-1861
 */
function pruefeWarenkorbStueckliste()
{
    trigger_error(__FUNCTION__ . ' is deprecated. This function should not be used anymore.', E_USER_DEPRECATED);
    return null;
}

/**
 * @deprecated since 5.0.0
 */
function pruefeKampagnenParameter()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Campaign::class . '::checkCampaignParameters() instead.',
        E_USER_DEPRECATED
    );
    Campaign::checkCampaignParameters();
}

/**
 * @param int         $definitionID
 * @param int         $key
 * @param float       $value
 * @param string|null $customData
 * @return int
 * @deprecated since 5.0.0
 */
function setzeKampagnenVorgang(int $definitionID, int $key, $value, $customData = null)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Campaign::class . '::setCampaignAction() instead.',
        E_USER_DEPRECATED
    );
    return Campaign::setCampaignAction($definitionID, $key, $value, $customData);
}

/**
 * @param string $salutation
 * @param int    $languageID
 * @param int    $customerID
 * @return mixed
 * @deprecated since 5.0.0
 */
function mappeKundenanrede($salutation, int $languageID, int $customerID = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Customer::class . '::mapSalutation() instead.',
        E_USER_DEPRECATED
    );
    return Customer::mapSalutation($salutation, $languageID, $customerID);
}

/**
 * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
 *
 * @param Zahlungsart|object $paymentMethod
 * @return bool
 * @deprecated since 5.0.0
 */
function aktiviereZahlungsart($paymentMethod)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PaymentMethod::class . '::activatePaymentMethod instead.',
        E_USER_DEPRECATED
    );
    return PaymentMethod::activatePaymentMethod($paymentMethod);
}

/**
 * @deprecated since 5.0.0
 */
function pruefeZahlungsartNutzbarkeit()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PaymentMethod::class . '::checkPaymentMethodAvailability instead.',
        E_USER_DEPRECATED
    );
    PaymentMethod::checkPaymentMethodAvailability();
}

/**
 * @return null
 * @deprecated since 5.0.0
 */
function gibTrustedShopsBewertenButton()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * Diese Funktion erhält einen Text als String und parsed ihn. Variablen die geparsed werden lauten wie folgt:
 * $#a:ID:NAME#$ => ID = kArtikel NAME => Wunschname ... wird in eine URL (evt. SEO) zum Artikel umgewandelt.
 * $#k:ID:NAME#$ => ID = kKategorie NAME => Wunschname ... wird in eine URL (evt. SEO) zur Kategorie umgewandelt.
 * $#h:ID:NAME#$ => ID = kHersteller NAME => Wunschname ... wird in eine URL (evt. SEO) zum Hersteller umgewandelt.
 * $#m:ID:NAME#$ => ID = kMerkmalWert NAME => Wunschname ... wird in eine URL (evt. SEO) zum MerkmalWert umgewandelt.
 * $#n:ID:NAME#$ => ID = kNews NAME => Wunschname ... wird in eine URL (evt. SEO) zur News umgewandelt.
 * $#l:ID:NAME#$ => ID = kSuchanfrage NAME => Wunschname ... wird in eine URL (evt. SEO) zur Livesuche umgewandelt.
 *
 * @param string $text
 * @return mixed
 * @deprecated since 5.0.0
 */
function parseNewsText($text)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return $text;
}

/**
 * Überprüft Parameter und gibt falls erfolgreich kWunschliste zurück, ansonten 0
 *
 * @return int
 * @deprecated since 5.0.0
 */
function checkeWunschlisteParameter()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Wishlist::class . ':checkeParameters() instead.',
        E_USER_DEPRECATED
    );
    return Wishlist::checkeParameters();
}

/**
 * @param Versandart|object $shippingMethod
 * @param string            $iso
 * @param string            $plz
 * @return object|null
 * @deprecated since 5.0.0
 */
function gibVersandZuschlag($shippingMethod, $iso, $plz)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getAdditionalFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getAdditionalFees($shippingMethod, $iso, $plz);
}

/**
 * @param Versandart|object $shippingMethod
 * @param String            $iso
 * @param Artikel|stdClass  $additionalProduct
 * @param Artikel|int       $product
 * @return int|string
 * @deprecated since 5.0.0
 */
function berechneVersandpreis($shippingMethod, $iso, $additionalProduct, $product = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::calculateShippingFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::calculateShippingFees($shippingMethod, $iso, $additionalProduct, $product);
}

/**
 * calculate shipping costs for exports
 *
 * @param string  $cISO
 * @param Artikel $product
 * @param int     $barzahlungZulassen
 * @param int     $customergGroupID
 * @return int
 * @deprecated since 5.0.0
 */
function gibGuenstigsteVersandkosten($cISO, $product, $barzahlungZulassen, $customergGroupID)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getLowestShippingFees() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getLowestShippingFees($cISO, $product, $barzahlungZulassen, $customergGroupID);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function setFsession()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function getFsession()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $filename
 * @return string
 * @deprecated since 5.0.0
 */
function guessCsvDelimiter($filename)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use getCsvDelimiter() instead.', E_USER_DEPRECATED);
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

    return getCsvDelimiter($filename);
}

/**
 * @param array|null $hookInfos
 * @param bool       $forceExit
 * @return array
 * @deprecated since 5.0.0
 */
function urlNotFoundRedirect(array $hookInfos = null, bool $forceExit = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Redirect::class . '::urlNotFoundRedirect() instead.',
        E_USER_DEPRECATED
    );
    return Redirect::urlNotFoundRedirect($hookInfos, $forceExit);
}

/**
 * @param int $minDeliveryDays
 * @param int $maxDeliveryDays
 * @return string
 * @deprecated since 5.0.0
 */
function getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getDeliverytimeEstimationText() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays);
}

/**
 * @param string      $metaProposal the proposed meta text value.
 * @param string|null $metaSuffix append suffix to meta value that wont be shortened
 * @param int|null    $maxLength $metaProposal will be truncated to $maxlength - mb_strlen($metaSuffix) characters
 * @return string truncated meta value with optional suffix (always appended if set),
 * @deprecated since 5.0.0
 */
function prepareMeta($metaProposal, $metaSuffix = null, $maxLength = null)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Metadata::class . '::prepareMeta() instead.',
        E_USER_DEPRECATED
    );
    return Metadata::prepareMeta($metaProposal, $metaSuffix, $maxLength);
}

/**
 * return trimmed description without (double) line breaks
 *
 * @param string $description
 * @return string
 * @deprecated since 5.0.0
 */
function truncateMetaDescription($description)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Metadata::class . '::truncateMetaDescription() instead.',
        E_USER_DEPRECATED
    );
    return Metadata::truncateMetaDescription($description);
}

/**
 * @param int  $kStueckliste
 * @param bool $assoc
 * @return array
 * @deprecated since 5.0.0
 */
function gibStuecklistenKomponente(int $kStueckliste, $assoc = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::getPartComponent() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::getPartComponent($kStueckliste, $assoc);
}

/**
 * @param object $NaviFilter
 * @param int    $count
 * @param bool   $seo
 * @deprecated since 5.0.0
 */
function doMainwordRedirect($NaviFilter, $count, $seo = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Redirect::class . '::doMainwordRedirect() instead.',
        E_USER_DEPRECATED
    );
    Redirect::doMainwordRedirect($NaviFilter, $count, $seo);
}

/**
 * Converts price into given currency
 *
 * @param float       $price
 * @param string|null $iso - EUR / USD
 * @param int|null    $id - kWaehrung
 * @param bool        $useRounding
 * @param int         $precision
 * @return float|bool
 * @deprecated since 5.0.0
 */
function convertCurrency($price, $iso = null, $id = null, $useRounding = true, $precision = 2)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Currency::class . '::convertCurrency() instead.',
        E_USER_DEPRECATED
    );
    return Currency::convertCurrency($price, $iso, $id, $useRounding, $precision);
}
/**
 * @param float $price
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisString($price)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return str_replace(',', '.', sprintf('%.2f', $price));
}

/**
 * @param string $languageCode
 * @param int    $languageID
 * @return int|string|bool
 * @deprecated since 5.0.0
 */
function gibSprachKeyISO($languageCode = '', int $languageID = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getLanguageDataByType() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getLanguageDataByType($languageCode, $languageID);
}

/**
 * @deprecated since 5.0.0
 */
function altenKuponNeuBerechnen()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Kupon::class . '::reCheck() instead.', E_USER_DEPRECATED);
    Kupon::reCheck();
}

/**
 * @param object $item
 * @param object $coupon
 * @return mixed
 * @deprecated since 5.0.0
 */
function checkeKuponWKPos($item, $coupon)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::checkCouponCartPositions() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::checkCouponCartItems($item, $coupon);
}

/**
 * @param object $item
 * @param object $coupon
 * @return mixed
 * @deprecated since 5.0.0
 */
function checkSetPercentCouponWKPos($item, $coupon)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::checkSetPercentCouponWKPos() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::checkSetPercentCouponWKPos($item, $coupon);
}

/**
 * @param int $kSteuerklasse
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibUst(int $kSteuerklasse)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Tax::class . '::getSalesTax() instead.', E_USER_DEPRECATED);
    return Tax::getSalesTax($kSteuerklasse);
}

/**
 * @param string|null $countryCode
 * @deprecated since 5.0.0
 */
function setzeSteuersaetze($countryCode = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Tax::class . '::setTaxRates() instead.', E_USER_DEPRECATED);
    Tax::setTaxRates($countryCode);
}

/**
 * @param array  $items
 * @param int    $net
 * @param int    $htmlCurrency
 * @param mixed int|object $oWaehrung
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlteSteuerpositionen($items, $net = -1, $htmlCurrency = 1, $currency = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Tax::class . '::getOldTaxPositions() instead.',
        E_USER_DEPRECATED
    );
    return Tax::getOldTaxItems($items, $net, $htmlCurrency, $currency);
}

/**
 * @param Versandart|object $shippingMethod
 * @param float             $cartTotal
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiString($shippingMethod, $cartTotal)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getShippingFreeString() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getShippingFreeString($shippingMethod, $cartTotal);
}

/**
 * @param Versandart $shippingMethod
 * @return string
 * @deprecated since 5.0.0
 */
function baueVersandkostenfreiLaenderString($shippingMethod)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getShippingFreeCountriesString() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getShippingFreeCountriesString($shippingMethod);
}

/**
 * gibt alle Sprachen zurück
 *
 * @param int $nOption
 * 0 = Normales Array
 * 1 = Gib ein Assoc mit Key = kSprache
 * 2 = Gib ein Assoc mit Key = cISO
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlleSprachen(int $nOption = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getAllLanguages() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getAllLanguages($nOption);
}

/**
 * @param bool     $shop
 * @param int|null $languageID - optional lang id to check against instead of session value
 * @return bool
 * @deprecated since 5.0.0
 */
function standardspracheAktiv($shop = false, $languageID = null)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::isDefaultLanguageActive() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::isDefaultLanguageActive($shop, $languageID);
}

/**
 * @param bool $bISO
 * @return string|int
 * @deprecated since 5.0.0
 */
function gibStandardWaehrung($bISO = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Session directly instead.', E_USER_DEPRECATED);
    return $bISO === true
        ? Frontend::getCurrency()->getCode()
        : Frontend::getCurrency()->getID();
}

/**
 * @param bool $bShop
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibStandardsprache($bShop = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getDefaultLanguage() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getDefaultLanguage($bShop);
}

/**
 * @deprecated since 5.0.0
 */
function resetNeuKundenKupon()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Kupon::class . '::resetNewCustomerCoupon() instead.',
        E_USER_DEPRECATED
    );
    Kupon::resetNewCustomerCoupon();
}

/**
 * Prüft ob reCaptcha mit private und public key konfiguriert ist
 * @return bool
 * @deprecated since 5.0.0
 */
function reCaptchaConfigured()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService::isConfigured() instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $response
 * @return bool
 * @deprecated since 5.0.0
 */
function validateReCaptcha($response)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService::validate() instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int $sec
 * @return string
 * @deprecated since 5.0.0
 */
function gibCaptchaCode($sec)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService instead.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int|string $sec
 * @return bool
 * @deprecated since 5.0.0 - use CaptchaService instead
 */
function generiereCaptchaCode($sec)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CaptchaService instead.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $klartext
 * @return string
 * @deprecated since 5.0.0
 */
function encodeCode($klartext)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . SimpleCaptchaService::class . '::encodeCode() instead.',
        E_USER_DEPRECATED
    );
    return SimpleCaptchaService::encodeCode($klartext);
}

/**
 * @param int    $customergGroupID
 * @param string $cLand
 * @return int|mixed
 * @deprecated since 5.0.0
 */
function gibVersandkostenfreiAb(int $customergGroupID, $cLand = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getFreeShippingMinimum() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getFreeShippingMinimum($customergGroupID, $cLand);
}

/**
 * @param float        $preis
 * @param int|Currency $waehrung
 * @param bool         $html
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisLocalizedOhneFaktor($preis, $waehrung = 0, $html = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Preise::class . '::getLocalizedPriceWithoutFactor() instead.',
        E_USER_DEPRECATED
    );
    return Preise::getLocalizedPriceWithoutFactor($preis, $waehrung, $html);
}

/**
 * @param float      $price
 * @param object|int $currency
 * @param int        $html
 * @param int        $decimals
 * @return string
 * @deprecated since 5.0.0
 */
function gibPreisStringLocalized($price, $currency = 0, $html = 1, $decimals = 2)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Preise::class . '::getLocalizedPriceString() instead.',
        E_USER_DEPRECATED
    );
    return Preise::getLocalizedPriceString($price, $currency, (bool)$html, $decimals);
}

/**
 * @param string $email
 * @return bool
 * @deprecated since 5.0.0
 */
function valid_email($email)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Text::class . '::filterEmailAddress() instead.',
        E_USER_DEPRECATED
    );
    return Text::filterEmailAddress($email) !== false;
}

/**
 * creates an csrf token
 *
 * @return string
 * @throws Exception
 * @deprecated since 5.0.0
 */
function generateCSRFToken()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->randomString(32);
}

/**
 * @param array $variBoxAnzahl_arr
 * @param int   $productID
 * @param bool  $bIstVater
 * @param bool  $bExtern
 * @deprecated since 5.0.0
 */
function fuegeVariBoxInWK($variBoxAnzahl_arr, $productID, $bIstVater, $bExtern = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::fuegeVariBoxInWK() instead.',
        E_USER_DEPRECATED
    );
    CartHelper::addVariboxToCart($variBoxAnzahl_arr, (int)$productID, (bool)$bIstVater, (bool)$bExtern);
}

/**
 * @param int    $productID
 * @param float  $fAnzahl
 * @param array  $oEigenschaftwerte_arr
 * @param bool   $cUnique
 * @param int    $kKonfigitem
 * @param int    $nPosTyp
 * @param string $cResponsibility
 * @deprecated since 5.0.0
 */
function fuegeEinInWarenkorbPers(
    $productID,
    $fAnzahl,
    $oEigenschaftwerte_arr,
    $cUnique = false,
    $kKonfigitem = 0,
    $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL,
    $cResponsibility = 'core'
) {
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PersistentCart::class . '::addToCheck() instead.',
        E_USER_DEPRECATED
    );
    PersistentCart::addToCheck(
        $productID,
        $fAnzahl,
        $oEigenschaftwerte_arr,
        $cUnique,
        $kKonfigitem,
        $nPosTyp,
        $cResponsibility
    );
}

/**
 * Gibt den kArtikel von einem Varikombi Kind zurück und braucht dafür Eigenschaften und EigenschaftsWerte
 * Klappt nur bei max. 2 Dimensionen
 *
 * @param int $productID
 * @param int $es0
 * @param int $esWert0
 * @param int $es1
 * @param int $esWert1
 * @return int
 * @deprecated since 5.0.0
 */
function findeKindArtikelZuEigenschaft($productID, $es0, $esWert0, $es1 = 0, $esWert1 = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Product::class . '::getChildProdctIDByAttribute() instead.',
        E_USER_DEPRECATED
    );
    return Product::getChildProductIDByAttribute($productID, $es0, $esWert0, $es1, $esWert1);
}

/**
 * @param int  $productID
 * @param bool $bSichtbarkeitBeachten
 * @return array
 * @deprecated since 5.0.0
 */
function gibVarKombiEigenschaftsWerte($productID, $bSichtbarkeitBeachten = true)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Product::class . '::getVarCombiAttributeValues() instead.',
        E_USER_DEPRECATED
    );
    return Product::getVarCombiAttributeValues((int)$productID, (bool)$bSichtbarkeitBeachten);
}

/**
 * @param float $price
 * @param float $taxRate
 * @param int   $precision
 * @return float
 * @deprecated since 5.0.0
 */
function berechneBrutto($price, $taxRate, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Tax::class . '::getGross() instead.', E_USER_DEPRECATED);
    return Tax::getGross($price, $taxRate, $precision);
}

/**
 * @param float $price
 * @param float $taxRate
 * @param int   $precision
 * @return float
 * @deprecated since 5.0.0
 */
function berechneNetto($price, $taxRate, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Tax::class . '::getNet() instead.', E_USER_DEPRECATED);
    return Tax::getNet($price, $taxRate, $precision);
}

/**
 * @param int           $productID
 * @param int           $qty
 * @param array         $attrValues
 * @param int           $redirect
 * @param bool          $unique
 * @param int           $kKonfigitem
 * @param stdClass|null $options
 * @param bool          $setzePositionsPreise
 * @param string        $responsibility
 * @return bool
 * @deprecated since 5.0.0
 */
function fuegeEinInWarenkorb(
    $productID,
    $qty,
    $attrValues = [],
    $redirect = 0,
    $unique = false,
    $kKonfigitem = 0,
    $options = null,
    $setzePositionsPreise = true,
    $responsibility = 'core'
) {
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . CartHelper::class . '::addProductIDToCart() instead.',
        E_USER_DEPRECATED
    );
    return CartHelper::addProductIDToCart(
        $productID,
        $qty,
        $attrValues,
        $redirect,
        $unique,
        $kKonfigitem,
        $options,
        $setzePositionsPreise,
        $responsibility
    );
}

/**
 * @param array $variations
 * @param int   $kEigenschaft
 * @param int   $kEigenschaftWert
 * @return bool|object
 * @deprecated since 5.0.0
 */
function findeVariation($variations, $kEigenschaft, $kEigenschaftWert)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Product::class . '::findVariation() instead.',
        E_USER_DEPRECATED
    );
    return Product::findVariation($variations, (int)$kEigenschaft, (int)$kEigenschaftWert);
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function getDefaultLanguageID()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getDefaultLanguage() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getDefaultLanguage()->kSprache;
}

/**
 * @param string $var
 * @return bool
 * @deprecated since 5.0.0
 */
function hasGPCDataInteger($var)
{
    return Request::hasGPCData($var);
}

/**
 * @param string $var
 * @return array
 * @deprecated since 5.0.0
 */
function verifyGPDataIntegerArray($var)
{
    return Request::verifyGPDataIntegerArray($var);
}

/**
 * @param string $var
 * @return int
 * @deprecated since 5.0.0
 */
function verifyGPCDataInteger($var)
{
    return Request::verifyGPCDataInt($var);
}

/**
 * @param string $var
 * @return string
 * @deprecated since 5.0.0
 */
function verifyGPDataString($var)
{
    return Request::verifyGPDataString($var);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function getRealIp()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Request::class . '::getRealIP() instead.', E_USER_DEPRECATED);
    return Request::getRealIP();
}

/**
 * @param bool $bBestellung
 * @return string
 * @deprecated since 5.0.0
 */
function gibIP($bBestellung = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Request::class . '::getIP() instead.', E_USER_DEPRECATED);
    return Request::getRealIP();
}

/**
 * Gibt einen String für einen Header mit dem angegebenen Status-Code aus
 *
 * @param int $nStatusCode
 * @return string
 * @deprecated since 5.0.0
 */
function makeHTTPHeader($nStatusCode)
{
    return Request::makeHTTPHeader((int)$nStatusCode);
}

/**
 * Prueft ob SSL aktiviert ist und auch durch Einstellung genutzt werden soll
 * -1 = SSL nicht aktiv und nicht erlaubt
 * 1 = SSL aktiv durch Einstellung nicht erwünscht
 * 2 = SSL aktiv und erlaubt
 * 4 = SSL nicht aktiv aber erzwungen
 *
 * @return int
 * @deprecated since 5.0.0
 */
function pruefeSSL()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Request::class . '::checkSSL() instead.', E_USER_DEPRECATED);
    return Request::checkSSL();
}

/**
 * @param Resource $ch
 * @param int $maxredirect
 * @return mixed
 * @deprecated since 5.0.0
 */
function curl_exec_follow($ch, int $maxredirect = 5)
{
    return Request::curl_exec_follow($ch, $maxredirect);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function http_get_contents($url, $timeout = 15, $post = null)
{
    return Request::make_http_request($url, $timeout, $post);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @return int
 * @deprecated since 5.0.0
 */
function http_get_status($url, $timeout = 15, $post = null)
{
    return Request::make_http_request($url, $timeout, $post, true);
}

/**
 * @param string $url
 * @param int    $timeout
 * @param null   $post
 * @param bool   $returnState - false = return content on success / true = return status code instead of content
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function make_http_request($url, $timeout = 15, $post = null, $returnState = false)
{
    return Request::make_http_request($url, $timeout, $post, $returnState);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function isAjaxRequest()
{
    return Request::isAjaxRequest();
}

/**
 * @param int  $customergGroupID
 * @param bool $bIgnoreSetting
 * @param bool $bForceAll
 * @return array
 * @deprecated since 5.0.0
 */
function gibBelieferbareLaender(int $customergGroupID = 0, bool $bIgnoreSetting = false, bool $bForceAll = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getPossibleShippingCountries() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getPossibleShippingCountries($customergGroupID, $bIgnoreSetting, $bForceAll);
}

/**
 * @param int $customergGroupID
 * @return array
 */
function gibMoeglicheVerpackungen($customergGroupID)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ShippingMethod::class . '::getPossiblePackagings() instead.',
        E_USER_DEPRECATED
    );
    return ShippingMethod::getPossiblePackagings($customergGroupID);
}

/**
 * @param int $size
 * @param string $format
 * @return string
 * @deprecated since 5.0.0
 */
function formatSize($size, $format = '%.2f')
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Text::class . '::formatSize() instead.', E_USER_DEPRECATED);
    return Text::formatSize($size, $format);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function createNavigation()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Navigation class instead.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int $languageID
 * @return array
 * @deprecated since 5.0.0
 */
function holeAlleSuchspecialOverlays(int $languageID = 0)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . SearchSpecial::class . '::getAll() instead.',
        E_USER_DEPRECATED
    );
    return SearchSpecial::getAll($languageID);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function baueAlleSuchspecialURLs()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . SearchSpecial::class . '::buildAllURLs() instead.',
        E_USER_DEPRECATED
    );
    return SearchSpecial::buildAllURLs();
}

/**
 * @param int $key
 * @return mixed|string
 * @deprecated since 5.0.0
 */
function baueSuchSpecialURL(int $key)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . SearchSpecial::class . '::buildURL() instead.',
        E_USER_DEPRECATED
    );
    return SearchSpecial::buildURL($key);
}

/**
 * Bekommmt ein Array von Objekten und baut ein assoziatives Array
 *
 * @param array $objects
 * @param string $keyName
 * @return array
 * @deprecated since 5.0.0
 */
function baueAssocArray(array $objects, $keyName)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Try \Functional\reindex() instead.', E_USER_DEPRECATED);
    $res = [];
    foreach ($objects as $item) {
        if (is_object($item)) {
            $members = array_keys(get_object_vars($item));
            if (is_array($members) && count($members) > 0) {
                $res[$item->$keyName] = new stdClass();
                foreach ($members as $oMember) {
                    $res[$item->$keyName]->$oMember = $item->$oMember;
                }
            }
        }
    }

    return $res;
}

/**
 * Erhält ein Array von Keys und fügt Sie zu einem String zusammen
 * wobei jeder Key durch den Seperator getrennt wird (z.b. ;1;5;6;).
 *
 * @param array  $cKey_arr
 * @param string $cSeperator
 * @return string
 * @deprecated since 5.0.0
 */
function gibKeyStringFuerKeyArray($cKey_arr, $cSeperator)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cKeys = '';
    if (is_array($cKey_arr) && count($cKey_arr) > 0 && mb_strlen($cSeperator) > 0) {
        $cKeys .= ';';
        foreach ($cKey_arr as $i => $cKey) {
            if ($i > 0) {
                $cKeys .= ';' . $cKey;
            } else {
                $cKeys .= $cKey;
            }
        }
        $cKeys .= ';';
    }

    return $cKeys;
}

/**
 * Bekommt einen String von Keys getrennt durch einen seperator (z.b. ;1;5;6;)
 * und gibt ein Array mit den Keys zurück
 *
 * @param string $cKeys
 * @param string $seperator
 * @return array
 * @deprecated since 5.0.0
 */
function gibKeyArrayFuerKeyString($cKeys, $seperator)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $keys = [];
    foreach (explode($seperator, $cKeys) as $cTMP) {
        if (mb_strlen($cTMP) > 0) {
            $keys[] = (int)$cTMP;
        }
    }

    return $keys;
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeMerkmalFilter($filter = [])
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ProductFilter::class . '::initCharacteristicFilter() instead.',
        E_USER_DEPRECATED
    );
    return ProductFilter::initCharacteristicFilter($filter);
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeSuchFilter($filter = [])
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . ProductFilter::class . '::initSearchFilter() instead.',
        E_USER_DEPRECATED
    );
    return ProductFilter::initSearchFilter($filter);
}

/**
 * @param array $filter
 * @return array
 * @deprecated since 5.0.0
 */
function setzeTagFilter($filter = [])
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Functionalitiy of product tags was removed in 5.0.0',
        E_USER_DEPRECATED
    );
    return [];
}

/**
 * @param int $languageID
 * @param int $customergGroupID
 * @return object|bool
 * @deprecated since 5.0.0
 */
function gibAGBWRB(int $languageID, int $customergGroupID)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LinkService::class . '::getAGBWRB() instead.',
        E_USER_DEPRECATED
    );
    return Shop::Container()->getLinkService()->getAGBWRB($languageID, $customergGroupID);
}

/**
 * @param string $text
 * @return string
 * @deprecated since 5.0.0
 */
function verschluesselXTEA($text)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService::encryptXTEA() instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->encryptXTEA($text);
}

/**
 * @param string $text
 * @return string
 * @deprecated since 5.0.0
 */
function entschluesselXTEA($text)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use CryptoService::decryptXTEA() instead.', E_USER_DEPRECATED);
    return Shop::Container()->getCryptoService()->decryptXTEA($text);
}

/**
 * @param object $obj
 * @param int    $art
 * @param int    $row
 * @param bool   $bForceNonSeo
 * @param bool   $bFull
 * @return string
 * @deprecated since 5.0.0
 */
function baueURL($obj, $art, $row = 0, $bForceNonSeo = false, $bFull = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . URL::class . '::buildURL() instead.',
        E_USER_DEPRECATED
    );
    return URL::buildURL($obj, $art, $bFull);
}

/**
 * @param object $obj
 * @param int    $art
 * @return array
 * @deprecated since 5.0.0
 */
function baueSprachURLS($obj, $art)
{
    trigger_error(__FUNCTION__ . ' is deprecated and doesn\'t do anything.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param array $products
 * @param int   $weightAcc
 * @param int   $shippingWeightAcc
 * @deprecated since 5.0.0 - not used in core anymore
 */
function baueGewicht(array $products, int $weightAcc = 2, int $shippingWeightAcc = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    foreach ($products as $product) {
        if ($product->fGewicht > 0) {
            $product->Versandgewicht    = str_replace('.', ',', round($product->fGewicht, $shippingWeightAcc));
            $product->Versandgewicht_en = round($product->fGewicht, $shippingWeightAcc);
        }
        if ($product->fArtikelgewicht > 0) {
            $product->Artikelgewicht    = str_replace('.', ',', round($product->fArtikelgewicht, $weightAcc));
            $product->Artikelgewicht_en = round($product->fArtikelgewicht, $weightAcc);
        }
    }
}

/**
 * Prüft ob eine die angegebende Email in temailblacklist vorhanden ist
 * Gibt true zurück, falls Email geblockt, ansonsten false
 *
 * @param string $mail
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeEmailblacklist(string $mail)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . SimpleMail::class . '::checkBlacklist() instead.',
        E_USER_DEPRECATED
    );
    return SimpleMail::checkBlacklist($mail);
}

/**
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibLetztenTokenDaten()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return isset($_SESSION['xcrsf_token'])
        ? json_decode($_SESSION['xcrsf_token'], true)
        : '';
}

/**
 * @param bool $old
 * @return string
 * @deprecated since 5.0.0
 */
function gibToken(bool $old = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($old) {
        $tokens = gibLetztenTokenDaten();
        if (!empty($tokens) && array_key_exists('token', $tokens)) {
            return $tokens['token'];
        }
    }

    return sha1(md5(microtime(true)) . (rand(0, 5000000000) * 1000));
}

/**
 * @param bool $old
 * @return string
 * @deprecated since 5.0.0
 */
function gibTokenName(bool $old = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($old) {
        $tokens = gibLetztenTokenDaten();
        if (!empty($tokens) && array_key_exists('name', $tokens)) {
            return $tokens['name'];
        }
    }

    return mb_substr(sha1(md5(microtime(true)) . (rand(0, 1000000000) * 1000)), 0, 4);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function validToken()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $name = gibTokenName(true);

    return isset($_POST[$name]) && gibToken(true) === $_POST[$name];
}

/**
 * @deprecated since 5.0.0
 */
function setzeSpracheUndWaehrungLink()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::generateLanguageAndCurrencyLinks() instead.',
        E_USER_DEPRECATED
    );
    $helper = LanguageHelper::getInstance();
    $helper->generateLanguageAndCurrencyLinks();
}

/**
 * @param string|array|object $data the string, array or object to convert recursively
 * @param bool                $encode true if data should be utf-8-encoded or false if data should be utf-8-decoded
 * @param bool                $copy false if objects should be changed, true if they should be cloned first
 * @return string|array|object converted data
 * @deprecated since 5.0.0
 */
function utf8_convert_recursive($data, $encode = true, $copy = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Text::class . '::utf8_convert_recursive() instead.',
        E_USER_DEPRECATED
    );
    return Text::utf8_convert_recursive($data, $encode, $copy);
}

/**
 * JSON-Encode $data only if it is not already encoded, meaning it avoids double encoding
 *
 * @param mixed $data
 * @return string|bool - false when $data is not encodable
 * @throws Exception
 * @deprecated since 5.0.0
 */
function json_safe_encode($data)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Text::class . '::json_safe_encode() instead.',
        E_USER_DEPRECATED
    );
    return Text::json_safe_encode($data);
}

/**
 * @param string $langISO
 * @deprecated since 5.0.0
 */
function checkeSpracheWaehrung($langISO = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Frontend::class . '::checkReset() instead.',
        E_USER_DEPRECATED
    );
    Frontend::checkReset($langISO);
}
/**
 * @param string $cISO
 * @return string
 * @deprecated since 5.0.0
 */
function ISO2land($cISO)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getCountryCodeByCountryName() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getCountryCodeByCountryName($cISO);
}

/**
 * @param string $cLand
 * @return string
 * @deprecated since 5.0.0
 */
function landISO($cLand)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . LanguageHelper::class . '::getIsoCodeByCountryName() instead.',
        E_USER_DEPRECATED
    );
    return LanguageHelper::getIsoCodeByCountryName($cLand);
}

/**
 * @return LinkGroupCollection
 * @deprecated since 5.0.0
 */
function setzeLinks()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Frontend::class . '::setSpecialLinks() instead.',
        E_USER_DEPRECATED
    );
    return Frontend::setSpecialLinks();
}
/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeSOAP($url = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PHPSettings::class . '::checkSOAP() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::checkSOAP($url);
}

/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeCURL($url = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PHPSettings::class . '::checkCURL() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::checkCURL($url);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeALLOWFOPEN()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PHPSettings::class . '::checkAllowFopen() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::checkAllowFopen();
}

/**
 * @param string $cSOCKETS
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeSOCKETS($cSOCKETS = '')
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PHPSettings::class . '::checkSockets() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::checkSockets($cSOCKETS);
}

/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function phpLinkCheck($url)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . PHPSettings::class . '::phpLinkCheck() instead.',
        E_USER_DEPRECATED
    );
    return PHPSettings::phpLinkCheck($url);
}

/**
 * @param DateTime|string|int $date
 * @param int $weekdays
 * @return DateTime
 * @deprecated since 5.0.0
 */
function dateAddWeekday($date, $weekdays)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Date::class . '::dateAddWeekday() instead.',
        E_USER_DEPRECATED
    );
    return Date::dateAddWeekday($date, $weekdays);
}

/**
 * @param array  $data
 * @param string $key
 * @param bool   $bStringToLower
 * @deprecated since 5.0.0
 */
function objectSort(&$data, $key, $bStringToLower = false)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . GeneralObject::class . '::sortBy() instead.',
        E_USER_DEPRECATED
    );
    GeneralObject::sortBy($data, $key, $bStringToLower);
}

/**
 * @param object $originalObj
 * @return stdClass
 * @deprecated since 5.0.0
 */
function kopiereMembers($originalObj)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . GeneralObject::class . '::kopiereMembers() instead.',
        E_USER_DEPRECATED
    );
    return GeneralObject::copyMembers($originalObj);
}

/**
 * @param stdClass|object $src
 * @param stdClass|object $dest
 * @deprecated since 5.0.0
 */
function memberCopy($src, &$dest)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . GeneralObject::class . '::memberCopy() instead.',
        E_USER_DEPRECATED
    );
    GeneralObject::memberCopy($src, $dest);
}

/**
 * @param object $object
 * @return mixed
 * @deprecated since 5.0.0
 */
function deepCopy($object)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . GeneralObject::class . '::deepCopy() instead.',
        E_USER_DEPRECATED
    );
    return GeneralObject::deepCopy($object);
}

/**
 * @param array $requestData
 * @return bool
 * @deprecated since 5.0.0
 */
function validateCaptcha(array $requestData)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Form::class . '::validateCaptcha() instead.',
        E_USER_DEPRECATED
    );
    return Form::validateCaptcha($requestData);
}

/**
 * create a hidden input field for xsrf validation
 * @return string
 * @throws Exception
 * @deprecated since 5.0.0
 */
function getTokenInput()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Form::class . '::getTokenInput() instead.',
        E_USER_DEPRECATED
    );
    return Form::getTokenInput();
}

/**
 * validate token from POST/GET
 * @return bool
 * @deprecated since 5.0.0
 */
function validateToken()
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Form::class . '::validateToken() instead.',
        E_USER_DEPRECATED
    );
    return Form::validateToken();
}

/**
 * @param array $fehlendeAngaben
 * @return int
 * @deprecated since 5.0.0
 */
function eingabenKorrekt($fehlendeAngaben)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Form::class . '::eingabenKorrekt() instead.',
        E_USER_DEPRECATED
    );
    return Form::eingabenKorrekt($fehlendeAngaben);
}

/**
 * @param string $dir
 * @return bool
 * @deprecated since 5.0.0
 */
function delDirRecursively(string $dir)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . FileSystem::class . '::delDirRecursively() instead.',
        E_USER_DEPRECATED
    );
    return FileSystem::delDirRecursively($dir);
}

/**
 * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
 *
 * @param string $cDatum
 * @return array
 * @deprecated since 5.0.0
 */
function gibDatumTeile(string $cDatum)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Date::class . '::getDateParts() instead.', E_USER_DEPRECATED);
    return Date::getDateParts($cDatum);
}
/**
 * @param Artikel $product
 * @param string $config
 * @return int
 * @deprecated since 5.0.0
 */
function gibVerfuegbarkeitsformularAnzeigen(Artikel $product, string $config): int
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Product::class . '::showAvailabilityForm() instead.',
        E_USER_DEPRECATED
    );
    return Product::showAvailabilityForm($product, $config);
}
/**
 * Besucher nach 3 Std in Besucherarchiv verschieben
 * @deprecated since 5.0.0
 */
function archiviereBesucher()
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ' . Visitor::class . '::archive() instead.', E_USER_DEPRECATED);
    Visitor::archive();
}

/**
 * Affiliate trennen
 *
 * @param string $seo
 * @return string
 * @deprecated since 5.0.0
 */
function extFremdeParameter($seo)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use ' . Request::class . '::extractExternalParams() instead.',
        E_USER_DEPRECATED
    );
    return Request::extractExternalParams($seo);
}

/**
 * @param string $cSQL
 * @param object $cSuchSQL
 * @param bool   $checkLanguage
 * @return array
 * @deprecated since 5.0.0
 */
function gibTagFreischalten($cSQL, $cSuchSQL, $checkLanguage = true)
{
    return [];
}
