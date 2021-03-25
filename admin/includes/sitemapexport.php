<?php

use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Media\Image\Product;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * @param string $file
 * @param mixed  $data
 * @deprecated since 5.0.0
 */
function baueSitemap($file, $data)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getLogService()->debug(
        'Baue "' . PFAD_EXPORT . 'sitemap_' .
        $file . '.xml", Datenlaenge ' . mb_strlen($data)
    );
    $conf = Shop::getSettings([CONF_SITEMAP]);
    if (!empty($data)) {
        if (function_exists('gzopen')) {
            // Sitemap-Dateien anlegen
            $gz = gzopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_' . $file . '.xml.gz', 'w9');
            fwrite($gz, getXMLHeader($conf['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            fwrite($gz, $data);
            fwrite($gz, '</urlset>');
            gzclose($gz);
        } else {
            // Sitemap-Dateien anlegen
            $handle = fopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_' . $file . '.xml', 'w+');
            fwrite($handle, getXMLHeader($conf['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            fwrite($handle, $data);
            fwrite($handle, '</urlset>');
            fclose($handle);
        }
    }
    $data = null;
}

/**
 * @param string $file
 * @param bool   $useGZ
 * @return string
 * @deprecated since 5.0.0
 */
function baueSitemapIndex($file, $useGZ)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $shopURL = Shop::getURL();
    $conf    = Shop::getSettings([CONF_SITEMAP]);
    $xml     = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml    .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    for ($i = 0; $i <= $file; ++$i) {
        if ($useGZ) {
            $xml .= '<sitemap><loc>' .
                Text::htmlentities($shopURL . '/' . PFAD_EXPORT . 'sitemap_' . $i . '.xml.gz') .
                '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod'])
                    || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? ('<lastmod>' . Text::htmlentities(date('Y-m-d')) . '</lastmod>') :
                    '') .
                '</sitemap>' . "\n";
        } else {
            $xml .= '<sitemap><loc>' . Text::htmlentities($shopURL . '/' .
                    PFAD_EXPORT . 'sitemap_' . $i . '.xml') . '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod'])
                    || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? ('<lastmod>' . Text::htmlentities(date('Y-m-d')) . '</lastmod>')
                    : '') .
                '</sitemap>' . "\n";
        }
    }
    $xml .= '</sitemapindex>';

    return $xml;
}

/**
 * @param string      $strLoc
 * @param null|string $strLastMod
 * @param null|string $strChangeFreq
 * @param null|string $strPriority
 * @param string      $googleImageURL
 * @param bool        $ssl
 * @deprecated since 5.0.0
 *
 * @return string
 */
function makeURL(
    $strLoc,
    $strLastMod = null,
    $strChangeFreq = null,
    $strPriority = null,
    $googleImageURL = '',
    $ssl = false
) {
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $strRet = "  <url>\n" .
        '     <loc>' . Text::htmlentities(Shop::getURL($ssl)) . '/' .
        Text::htmlentities($strLoc) . "</loc>\n";
    if (mb_strlen($googleImageURL) > 0) {
        $strRet .=
            "     <image:image>\n" .
            '        <image:loc>' . Text::htmlentities($googleImageURL) . "</image:loc>\n" .
            "     </image:image>\n";
    }
    if ($strLastMod) {
        $strRet .= '     <lastmod>' . Text::htmlentities($strLastMod) . "</lastmod>\n";
    }
    if ($strChangeFreq) {
        $strRet .= '     <changefreq>' . Text::htmlentities($strChangeFreq) . "</changefreq>\n";
    }
    if ($strPriority) {
        $strRet .= '     <priority>' . Text::htmlentities($strPriority) . "</priority>\n";
    }
    $strRet .= "  </url>\n";

    return $strRet;
}

/**
 * @param string $iso
 * @param array  $languages
 * @return bool
 * @deprecated since 5.0.0
 */
function spracheEnthalten($iso, $languages)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($_SESSION['cISOSprache'] === $iso) {
        return true;
    }
    if (is_array($languages)) {
        foreach ($languages as $SpracheTMP) {
            if ($SpracheTMP->cISO === $iso) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param string $url
 * @return bool
 * @deprecated since 5.0.0
 */
function isSitemapBlocked($url)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $excludes = [
        'navi.php',
        'suche.php',
        'jtl.php',
        'pass.php',
        'registrieren.php',
        'warenkorb.php',
    ];

    foreach ($excludes as $exclude) {
        if (mb_strpos($url, $exclude) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * @deprecated since 5.0.0
 */
function generateSitemapXML()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getLogService()->debug('Sitemap wird erstellt');
    $timeStart = microtime(true);
    $conf      = Shop::getSettings([
        CONF_ARTIKELUEBERSICHT,
        CONF_SITEMAP,
        CONF_GLOBAL,
        CONF_NAVIGATIONSFILTER,
        CONF_BOXEN
    ]);
    require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
    if (!isset($conf['sitemap']['sitemap_insert_lastmod'])) {
        $conf['sitemap']['sitemap_insert_lastmod'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_insert_changefreq'])) {
        $conf['sitemap']['sitemap_insert_changefreq'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_insert_priority'])) {
        $conf['sitemap']['sitemap_insert_priority'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_google_ping'])) {
        $conf['sitemap']['sitemap_google_ping'] = 'N';
    }
    $addChangeFreq = $conf['sitemap']['sitemap_insert_changefreq'] === 'Y';
    $addPriority   = $conf['sitemap']['sitemap_insert_priority'] === 'Y';
    // W3C Datetime formats:
    //  YYYY-MM-DD (eg 1997-07-16)
    //  YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
    $defaultCustomerGroupID  = CustomerGroup::getDefaultGroupID();
    $languages               = LanguageHelper::getAllLanguages();
    $languageAssoc           = gibAlleSprachenAssoc($languages);
    $defaultLang             = LanguageHelper::getDefaultLanguage(true);
    $defaultLangID           = (int)$defaultLang->kSprache;
    $_SESSION['kSprache']    = $defaultLangID;
    $_SESSION['cISOSprache'] = $defaultLang->cISO;
    Tax::setTaxRates();
    if (!isset($_SESSION['Kundengruppe'])) {
        $_SESSION['Kundengruppe'] = new CustomerGroup();
    }
    $_SESSION['Kundengruppe']->setID($defaultCustomerGroupID);
    // Stat Array
    $stats = [
        'artikel'          => 0,
        'artikelbild'      => 0,
        'artikelsprache'   => 0,
        'link'             => 0,
        'lategorie'        => 0,
        'kategoriesprache' => 0,
        'tag'              => 0,
        'tagsprache'       => 0,
        'hersteller'       => 0,
        'livesuche'        => 0,
        'livesuchesprache' => 0,
        'merkmal'          => 0,
        'merkmalsprache'   => 0,
        'news'             => 0,
        'newskategorie'    => 0,
    ];
    // Artikelübersicht - max. Artikel pro Seite
    $nArtikelProSeite = ((int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0)
        ? (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite']
        : 20;
    if ($conf['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'Y') {
        $nStdDarstellung = (int)$conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
            ? (int)$conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']
            : ERWDARSTELLUNG_ANSICHT_LISTE;
        if ($nStdDarstellung > 0) {
            switch ($nStdDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    break;
            }
        }
    }
    $fileNumber    = 0;
    $sitemapNumber = 1;
    $urlCounts     = [];
    $sitemapLimit  = 25000;
    $sitemapData   = '';
    $imageBaseURL  = Shop::getImageBaseURL();
    $db            = Shop::Container()->getDB();
    $sitemapData  .= makeURL('', null, $addChangeFreq ? FREQ_ALWAYS : null, $addPriority ? PRIO_VERYHIGH : null);
    //Alte Sitemaps löschen
    loescheSitemaps();
    $andWhere = '';
    // Kindartikel?
    if ($conf['sitemap']['sitemap_varkombi_children_export'] !== 'Y') {
        $andWhere .= ' AND tartikel.kVaterArtikel = 0';
    }
    // Artikelanzeigefilter
    if ((int)$conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
        // 'Nur Artikel mit Lagerbestand>0 anzeigen'
        $andWhere .= " AND (tartikel.cLagerBeachten = 'N' OR tartikel.fLagerbestand > 0)";
    } elseif ((int)$conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
        // 'Nur Artikel mit Lagerbestand>0 oder deren Lagerbestand<0 werden darf'
        $andWhere .= " AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.cLagerKleinerNull = 'Y' 
                            OR tartikel.fLagerbestand > 0)";
    }
    //Artikel STD Sprache
    $modification = $conf['sitemap']['sitemap_insert_lastmod'] === 'Y'
        ? ', tartikel.dLetzteAktualisierung'
        : '';
    $res          = $db->queryPrepared(
        'SELECT tartikel.kArtikel, tartikel.cName, tseo.cSeo, tartikel.cArtNr' .
        $modification . "
            FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :kGrpID 
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :langID
            WHERE tartikelsichtbarkeit.kArtikel IS NULL" . $andWhere,
        [
            'kGrpID' => $defaultCustomerGroupID,
            'langID' => $defaultLangID
        ],
        ReturnType::QUERYSINGLE
    );
    while (($product = $res->fetch(PDO::FETCH_OBJ)) !== false) {
        if ($sitemapNumber > $sitemapLimit) {
            $sitemapNumber = 1;
            baueSitemap($fileNumber, $sitemapData);
            ++$fileNumber;
            $urlCounts[$fileNumber] = 0;
            $sitemapData            = '';
        }
        // GoogleImages einbinden?
        $image = '';
        if ($conf['sitemap']['sitemap_googleimage_anzeigen'] === 'Y'
            && ($number = Product::getPrimaryNumber($product->kArtikel, $db)) !== null
        ) {
            $image = Product::getThumb(
                Image::TYPE_PRODUCT,
                $product->kArtikel,
                $product,
                Image::SIZE_LG,
                $number
            );
            if (mb_strlen($image) > 0) {
                $image = $imageBaseURL . $image;
            }
        }
        $url = URL::buildURL($product, URLART_ARTIKEL);
        if (!isSitemapBlocked($url)) {
            $sitemapData .= makeURL(
                $url,
                (($conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? date_format(date_create($product->dLetzteAktualisierung), 'c')
                    : null),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null,
                $image
            );
            ++$sitemapNumber;
            if (!isset($urlCounts[$fileNumber])) {
                $urlCounts[$fileNumber] = 0;
            }
            ++$urlCounts[$fileNumber];
            ++$stats['artikelbild'];
        }
    }
    // Artikel sonstige Sprachen
    foreach ($languages as $tmpLang) {
        if ($tmpLang->kSprache === $defaultLangID) {
            continue;
        }
        $res = $db->queryPrepared(
            "SELECT tartikel.kArtikel, tartikel.dLetzteAktualisierung, tseo.cSeo
                FROM tartikelsprache, tartikel
                JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :langID
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :kGrpID
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tartikelsprache.kArtikel
                    AND tartikel.kVaterArtikel = 0 
                    AND tartikelsprache.kSprache = :langID
                ORDER BY tartikel.kArtikel",
            [
                'kGrpID' => $defaultCustomerGroupID,
                'langID' => $tmpLang->kSprache
            ],
            ReturnType::QUERYSINGLE
        );
        while (($product = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if ($sitemapNumber > $sitemapLimit) {
                $sitemapNumber = 1;
                baueSitemap($fileNumber, $sitemapData);
                ++$fileNumber;
                $urlCounts[$fileNumber] = 0;
                $sitemapData            = '';
            }
            $image = '';
            if ($conf['sitemap']['sitemap_googleimage_anzeigen'] === 'Y'
                && ($number = Product::getPrimaryNumber($product->kArtikel, $db)) !== null
            ) {
                $image = Product::getThumb(
                    Image::TYPE_PRODUCT,
                    $product->kArtikel,
                    $product,
                    Image::SIZE_LG,
                    $number
                );
                if (mb_strlen($image) > 0) {
                    $image = $imageBaseURL . $image;
                }
            }
            $url = URL::buildURL($product, URLART_ARTIKEL);
            if (!isSitemapBlocked($url)) {
                $sitemapData .= makeURL(
                    $url,
                    date_format(date_create($product->dLetzteAktualisierung), 'c'),
                    $addChangeFreq ? FREQ_DAILY : null,
                    $addPriority ? PRIO_HIGH : null,
                    $image
                );
                ++$sitemapNumber;
                ++$urlCounts[$fileNumber];
                ++$stats['artikelsprache'];
            }
        }
    }

    if ($conf['sitemap']['sitemap_seiten_anzeigen'] === 'Y') {
        // Links alle sprachen
        $res = $db->queryPrepared(
            "SELECT tlink.nLinkart, tlinksprache.kLink, tlinksprache.cISOSprache, tlink.bSSL
                FROM tlink
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                JOIN tlinkgruppe 
                    ON tlinkgroupassociations.linkGroupID = tlinkgruppe.kLinkgruppe
                JOIN tlinksprache
                    ON tlinksprache.kLink = tlink.kLink
                WHERE tlink.cSichtbarNachLogin = 'N'
                    AND tlink.cNoFollow = 'N'
                    AND tlinkgruppe.cName != 'hidden'
                    AND tlinkgruppe.cTemplatename != 'hidden'
                    AND (tlink.cKundengruppen IS NULL
                    OR tlink.cKundengruppen = 'NULL'
                    OR FIND_IN_SET(:cGrpID, REPLACE(tlink.cKundengruppen, ';', ',')) > 0)
                ORDER BY tlinksprache.kLink",
            ['cGrpID' => $defaultCustomerGroupID],
            ReturnType::QUERYSINGLE
        );
        while (($tlink = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if (spracheEnthalten($tlink->cISOSprache, $languages)) {
                $oSeo = $db->queryPrepared(
                    "SELECT cSeo
                        FROM tseo
                        WHERE cKey = 'kLink'
                            AND kKey = :linkID
                            AND kSprache = :langID",
                    [
                        'linkID' => $tlink->kLink,
                        'langID' => $languageAssoc[$tlink->cISOSprache]
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($oSeo->cSeo) && mb_strlen($oSeo->cSeo) > 0) {
                    $tlink->cSeo = $oSeo->cSeo;
                }

                if (isset($tlink->cSeo) && mb_strlen($tlink->cSeo) > 0) {
                    if ($sitemapNumber > $sitemapLimit) {
                        $sitemapNumber = 1;
                        baueSitemap($fileNumber, $sitemapData);
                        ++$fileNumber;
                        $urlCounts[$fileNumber] = 0;
                        $sitemapData            = '';
                    }

                    $tlink->cLocalizedSeo[$tlink->cISOSprache] = $tlink->cSeo ?? null;
                    $link                                      = URL::buildURL($tlink, URLART_SEITE);
                    if (mb_strlen($tlink->cSeo) > 0) {
                        $link = $tlink->cSeo;
                    } elseif ($_SESSION['cISOSprache'] !== $tlink->cISOSprache) {
                        $link .= '&lang=' . $tlink->cISOSprache;
                    }
                    if (!isSitemapBlocked($link)) {
                        $sitemapData .= makeURL(
                            $link,
                            null,
                            $addChangeFreq ? FREQ_MONTHLY : null,
                            $addPriority ? PRIO_LOW : null,
                            '',
                            (int)$tlink->bSSL === 2
                        );
                        ++$sitemapNumber;
                        ++$urlCounts[$fileNumber];
                        ++$stats['link'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
        $categoryHelper = new KategorieListe();
        // Kategorien STD Sprache
        $res = $db->queryPrepared(
            "SELECT tkategorie.kKategorie, tseo.cSeo, tkategorie.dLetzteAktualisierung
                FROM tkategorie
                JOIN tseo 
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = tkategorie.kKategorie
                    AND tseo.kSprache = :langID
                LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                ORDER BY tkategorie.kKategorie",
            [
                'langID' => $defaultLangID,
                'cGrpID' => $defaultCustomerGroupID
            ],
            ReturnType::QUERYSINGLE
        );
        while (($categoryData = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $urls = baueExportURL(
                $categoryData->kKategorie,
                'kKategorie',
                date_format(date_create($categoryData->dLetzteAktualisierung), 'c'),
                $languages,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($urls as $catURL) {
                if ($categoryHelper->nichtLeer($categoryData->kKategorie, $defaultCustomerGroupID) === true) {
                    if ($sitemapNumber > $sitemapLimit) {
                        $sitemapNumber = 1;
                        baueSitemap($fileNumber, $sitemapData);
                        ++$fileNumber;
                        $urlCounts[$fileNumber] = 0;
                        $sitemapData            = '';
                    }
                    if (!isSitemapBlocked($catURL)) {
                        $sitemapData .= $catURL;
                        ++$sitemapNumber;
                        ++$urlCounts[$fileNumber];
                        ++$stats['kategorie'];
                    }
                }
            }
        }
        // Kategorien sonstige Sprachen
        foreach ($languages as $tmpLang) {
            $res = $db->queryPrepared(
                "SELECT tkategorie.kKategorie, tkategorie.dLetzteAktualisierung, tseo.cSeo
                    FROM tkategoriesprache, tkategorie
                    JOIN tseo 
                        ON tseo.cKey = 'kKategorie'
                        AND tseo.kKey = tkategorie.kKategorie
                        AND tseo.kSprache = :langID
                    LEFT JOIN tkategoriesichtbarkeit 
                        ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID 
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND tkategorie.kKategorie = tkategoriesprache.kKategorie
                        AND tkategoriesprache.kSprache = :langID
                    ORDER BY tkategorie.kKategorie",
                [
                    'langID' => $tmpLang->kSprache,
                    'cGrpID' => $defaultCustomerGroupID
                ],
                ReturnType::QUERYSINGLE
            );
            while (($categoryData = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $urls = baueExportURL(
                    $categoryData->kKategorie,
                    'kKategorie',
                    date_format(date_create($categoryData->dLetzteAktualisierung), 'c'),
                    $languages,
                    $tmpLang->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($urls as $catURL) { // X viele Seiten durchlaufen
                    if ($categoryHelper->nichtLeer($categoryData->kKategorie, $defaultCustomerGroupID) === true) {
                        if ($sitemapNumber > $sitemapLimit) {
                            $sitemapNumber = 1;
                            baueSitemap($fileNumber, $sitemapData);
                            ++$fileNumber;
                            $urlCounts[$fileNumber] = 0;
                            $sitemapData            = '';
                        }
                        if (!isSitemapBlocked($catURL)) {
                            $sitemapData .= $catURL;
                            ++$sitemapNumber;
                            ++$urlCounts[$fileNumber];
                            ++$stats['kategoriesprache'];
                        }
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
        // Hersteller
        $res = $db->queryPrepared(
            "SELECT thersteller.kHersteller, thersteller.cName, tseo.cSeo
                FROM thersteller
                JOIN tseo 
                    ON tseo.cKey = 'kHersteller'
                    AND tseo.kKey = thersteller.kHersteller
                    AND tseo.kSprache = :langID
                ORDER BY thersteller.kHersteller",
            ['langID' => $defaultLangID],
            ReturnType::QUERYSINGLE
        );
        while (($oHersteller = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $urls = baueExportURL(
                $oHersteller->kHersteller,
                'kHersteller',
                null,
                $languages,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($urls as $catURL) {
                if ($sitemapNumber > $sitemapLimit) {
                    $sitemapNumber = 1;
                    baueSitemap($fileNumber, $sitemapData);
                    ++$fileNumber;
                    $urlCounts[$fileNumber] = 0;
                    $sitemapData            = '';
                }
                if (!isSitemapBlocked($catURL)) {
                    $sitemapData .= $catURL;
                    ++$sitemapNumber;
                    ++$urlCounts[$fileNumber];
                    ++$stats['hersteller'];
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_livesuche_anzeigen'] === 'Y') {
        // Livesuche STD Sprache
        $res = $db->queryPrepared(
            "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht
                FROM tsuchanfrage
                JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                    AND tseo.kSprache = :langID
                WHERE tsuchanfrage.kSprache = :langID
                    AND tsuchanfrage.nAktiv = 1
                ORDER BY tsuchanfrage.kSuchanfrage",
            ['langID' => $defaultLangID],
            ReturnType::QUERYSINGLE
        );
        while (($oSuchanfrage = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $urls = baueExportURL(
                $oSuchanfrage->kSuchanfrage,
                'kSuchanfrage',
                null,
                $languages,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($urls as $catURL) {
                if ($sitemapNumber > $sitemapLimit) {
                    $sitemapNumber = 1;
                    baueSitemap($fileNumber, $sitemapData);
                    ++$fileNumber;
                    $urlCounts[$fileNumber] = 0;
                    $sitemapData            = '';
                }
                if (!isSitemapBlocked($catURL)) {
                    $sitemapData .= $catURL;
                    ++$sitemapNumber;
                    ++$urlCounts[$fileNumber];
                    ++$stats['livesuche'];
                }
            }
        }
        // Livesuche sonstige Sprachen
        foreach ($languages as $tmpLang) {
            if ($tmpLang->kSprache === $defaultLangID) {
                continue;
            }
            $res = $db->queryPrepared(
                "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht
                    FROM tsuchanfrage
                    JOIN tseo 
                        ON tseo.cKey = 'kSuchanfrage'
                        AND tseo.kKey = tsuchanfrage.kSuchanfrage
                        AND tseo.kSprache = :langID
                    WHERE tsuchanfrage.kSprache = :langID
                        AND tsuchanfrage.nAktiv = 1
                    ORDER BY tsuchanfrage.kSuchanfrage",
                ['langID' => $tmpLang->kSprache],
                ReturnType::QUERYSINGLE
            );
            while (($oSuchanfrage = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $urls = baueExportURL(
                    $oSuchanfrage->kSuchanfrage,
                    'kSuchanfrage',
                    null,
                    $languages,
                    $tmpLang->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($urls as $catURL) { // X viele Seiten durchlaufen
                    if ($sitemapNumber > $sitemapLimit) {
                        $sitemapNumber = 1;
                        baueSitemap($fileNumber, $sitemapData);
                        ++$fileNumber;
                        $urlCounts[$fileNumber] = 0;
                        $sitemapData            = '';
                    }
                    if (!isSitemapBlocked($catURL)) {
                        $sitemapData .= $catURL;
                        ++$sitemapNumber;
                        ++$urlCounts[$fileNumber];
                        ++$stats['livesuchesprache'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_news_anzeigen'] === 'Y') {
        $res = $db->query(
            "SELECT tnews.*, tseo.cSeo, tseo.kSprache
                FROM tnews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = t.languageID
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                    OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID() .
                        "', REPLACE(tnews.cKundengruppe, ';',',')) > 0) 
                    ORDER BY tnews.dErstellt",
            ReturnType::QUERYSINGLE
        );
        while (($oNews = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $catURL = makeURL(
                URL::buildURL($oNews, URLART_NEWS),
                date_format(date_create($oNews->dGueltigVon), 'c'),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null
            );
            if ($sitemapNumber > $sitemapLimit) {
                $sitemapNumber = 1;
                baueSitemap($fileNumber, $sitemapData);
                ++$fileNumber;
                $urlCounts[$fileNumber] = 0;
                $sitemapData            = '';
            }
            if (!isSitemapBlocked($catURL)) {
                $sitemapData .= $catURL;
                ++$sitemapNumber;
                ++$urlCounts[$fileNumber];
                ++$stats['news'];
            }
        }
    }
    if ($conf['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
        $res = $db->query(
            "SELECT tnewskategorie.*, tseo.cSeo, tseo.kSprache
                 FROM tnewskategorie
                 JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                 JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = t.languageID
                 WHERE tnewskategorie.nAktiv = 1",
            ReturnType::QUERYSINGLE
        );

        while (($oNewsKategorie = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $catURL = makeURL(
                URL::buildURL($oNewsKategorie, URLART_NEWSKATEGORIE),
                date_format(date_create($oNewsKategorie->dLetzteAktualisierung), 'c'),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null
            );
            if ($sitemapNumber > $sitemapLimit) {
                $sitemapNumber = 1;
                baueSitemap($fileNumber, $sitemapData);
                ++$fileNumber;
                $urlCounts[$fileNumber] = 0;
                $sitemapData            = '';
            }
            if (!isSitemapBlocked($catURL)) {
                $sitemapData .= $catURL;
                ++$sitemapNumber;
                ++$urlCounts[$fileNumber];
                ++$stats['newskategorie'];
            }
        }
    }
    baueSitemap($fileNumber, $sitemapData);
    // XML ablegen + ausgabe an user
    $fileName = PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml';
    if (is_writable($fileName) || !is_file($fileName)) {
        $useGZ = function_exists('gzopen');
        // Sitemap Index Datei anlegen
        $file = fopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml', 'w+');
        fwrite($file, baueSitemapIndex($fileNumber, $useGZ));
        fclose($file);
        $timeEnd   = microtime(true);
        $timeTotal = $timeEnd - $timeStart;
        executeHook(HOOK_SITEMAP_EXPORT_GENERATED, ['nAnzahlURL_arr' => $urlCounts, 'fTotalZeit' => $timeTotal]);
        // Sitemap Report
        baueSitemapReport($urlCounts, $timeTotal);
        // ping sitemap to Google and Bing
        if ($conf['sitemap']['sitemap_google_ping'] === 'Y') {
            $encodedSitemapIndexURL = urlencode(Shop::getURL() . '/sitemap_index.xml');
            if (($httpStatus = Request::http_get_status(
                'http://www.google.com/webmasters/tools/ping?sitemap=' . $encodedSitemapIndexURL
            )) !== 200) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Google failed with status ' . $httpStatus);
            }
            if (($httpStatus = Request::http_get_status(
                'http://www.bing.com/ping?sitemap=' . $encodedSitemapIndexURL
            )) !== 200) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Bing failed with status ' . $httpStatus);
            }
        }
    }
}

/**
 * @param string $googleImageConfig
 * @return string
 * @deprecated since 5.0.0
 */
function getXMLHeader($googleImageConfig)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

    if ($googleImageConfig === 'Y') {
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
    }

    $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    return $xml;
}

/**
 * @param stdClass $productData
 * @return string|null
 * @deprecated since 5.0.0
 */
function holeGoogleImage($productData)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $product           = new Artikel();
    $product->kArtikel = $productData->kArtikel;
    $product->holArtikelAttribute();
    // Prüfe ob Funktionsattribut "artikelbildlink" ART_ATTRIBUT_BILDLINK gesetzt ist
    // Falls ja, lade die Bilder des anderen Artikels
    $image = new stdClass();
    if (isset($product->FunktionsAttribute[ART_ATTRIBUT_BILDLINK])
        && mb_strlen($product->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]) > 0
    ) {
        $artNo = Text::filterXSS($product->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]);
        $image = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikelpict.cPfad
                FROM tartikelpict
                JOIN tartikel 
                    ON tartikel.cArtNr = :artNr
                WHERE tartikelpict.kArtikel = tartikel.kArtikel
                GROUP BY tartikelpict.cPfad
                ORDER BY tartikelpict.nNr
                LIMIT 1',
            ['artNr' => $artNo],
            ReturnType::SINGLE_OBJECT
        );
    }

    if (empty($image->cPfad)) {
        $image = Shop::Container()->getDB()->queryPrepared(
            'SELECT cPfad 
                FROM tartikelpict 
                WHERE kArtikel = :articleID 
                GROUP BY cPfad 
                ORDER BY nNr 
                LIMIT 1',
            ['articleID' => (int)$product->kArtikel],
            ReturnType::SINGLE_OBJECT
        );
    }

    return $image->cPfad ?? null;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheSitemaps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_dir(PFAD_ROOT . PFAD_EXPORT) && $dh = opendir(PFAD_ROOT . PFAD_EXPORT)) {
        while (($file = readdir($dh)) !== false) {
            if ($file === 'sitemap_index.xml' || mb_strpos($file, 'sitemap_') !== false) {
                unlink(PFAD_ROOT . PFAD_EXPORT . $file);
            }
        }

        closedir($dh);

        return true;
    }

    return false;
}

/**
 * @param array $urlCounts
 * @param float $totalTime
 * @deprecated since 5.0.0
 */
function baueSitemapReport($urlCounts, $totalTime)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($totalTime > 0 && is_array($urlCounts) && count($urlCounts) > 0) {
        $totalURLcount = 0;
        foreach ($urlCounts as $urlCount) {
            $totalURLcount += $urlCount;
        }
        $report                     = new stdClass();
        $report->nTotalURL          = $totalURLcount;
        $report->fVerarbeitungszeit = number_format($totalTime, 2);
        $report->dErstellt          = 'NOW()';

        $reportID = Shop::Container()->getDB()->insert('tsitemapreport', $report);
        $useGZ    = function_exists('gzopen');
        Shop::Container()->getLogService()->debug('Sitemaps Report: ' . var_export($urlCounts, true));
        foreach ($urlCounts as $i => $urlCount) {
            if ($urlCount <= 0) {
                continue;
            }
            $reportFile                 = new stdClass();
            $reportFile->kSitemapReport = $reportID;
            $reportFile->cDatei         = $useGZ
                ? ('sitemap_' . $i . '.xml.gz')
                : ('sitemap_' . $i . '.xml');
            $reportFile->nAnzahlURL     = $urlCount;
            $file                       = PFAD_ROOT . PFAD_EXPORT . $reportFile->cDatei;
            $reportFile->fGroesse       = is_file($file)
                ? number_format(filesize(PFAD_ROOT . PFAD_EXPORT . $reportFile->cDatei) / 1024, 2)
                : 0;
            Shop::Container()->getDB()->insert('tsitemapreportfile', $reportFile);
        }
    }
}

/**
 * @param int        $keyID
 * @param string     $keyName
 * @param string     $lastUpdate
 * @param array      $languages
 * @param int        $langID
 * @param int        $productsPerPage
 * @param array|null $config
 * @return array
 * @deprecated since 5.0.0
 */
function baueExportURL(int $keyID, $keyName, $lastUpdate, $languages, $langID, $productsPerPage, $config = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $config = $config ?? Shopsetting::getInstance()->getAll();
    $urls   = [];
    $params = [];
    Shop::setLanguage($langID);
    $filterConfig = new Config();
    $filterConfig->setLanguageID($langID);
    $filterConfig->setLanguages($languages);
    $filterConfig->setConfig($config);
    $filterConfig->setCustomerGroupID(Frontend::getCustomerGroup()->getID());
    $filterConfig->setBaseURL(Shop::getURL() . '/');
    $naviFilter = new ProductFilter($filterConfig, Shop::Container()->getDB(), Shop::Container()->getCache());
    switch ($keyName) {
        case 'kKategorie':
            $params['kKategorie'] = $keyID;
            $naviFilter->initStates($params);
            break;

        case 'kHersteller':
            $params['kHersteller'] = $keyID;
            $naviFilter->initStates($params);
            break;

        case 'kSuchanfrage':
            $params['kSuchanfrage'] = $keyID;
            $naviFilter->initStates($params);
            if ($keyID > 0) {
                $oSuchanfrage = Shop::Container()->getDB()->queryPrepared(
                    'SELECT cSuche
                        FROM tsuchanfrage
                        WHERE kSuchanfrage = :ks
                        ORDER BY kSuchanfrage',
                    ['ks' => $keyID],
                    ReturnType::SINGLE_OBJECT
                );
                if (!empty($oSuchanfrage->cSuche)) {
                    $naviFilter->getSearchQuery()->setID($keyID)->setName($oSuchanfrage->cSuche);
                }
            }
            break;

        case 'kMerkmalWert':
            $params['kMerkmalWert'] = $keyID;
            $naviFilter->initStates($params);
            break;

        case 'kSuchspecial':
            $params['kSuchspecial'] = $keyID;
            $naviFilter->initStates($params);
            break;

        default:
            return $urls;
    }
    $oSuchergebnisse = $naviFilter->generateSearchResults(null, false, (int)$productsPerPage);
    $shopURL         = Shop::getURL();
    $shopURLSSL      = Shop::getURL(true);
    $search          = [$shopURL . '/', $shopURLSSL . '/'];
    $replace         = ['', ''];
    if (($keyName === 'kKategorie' && $keyID > 0) || $oSuchergebnisse->getProductCount() > 0) {
        $urls[] = makeURL(
            str_replace($search, $replace, $naviFilter->getFilterURL()->getURL()),
            $lastUpdate,
            $config['sitemap']['sitemap_insert_changefreq'] === 'Y' ? FREQ_WEEKLY : null,
            $config ['sitemap']['sitemap_insert_priority'] === 'Y' ? PRIO_NORMAL : null
        );
    }

    return $urls;
}

/**
 * @param array $languages
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlleSprachenAssoc($languages)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $assoc = [];
    foreach ($languages as $language) {
        $assoc[$language->cISO] = (int)$language->kSprache;
    }

    return $assoc;
}
