<?php

use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Shop;

/**
 * @return bool
 */
function generiereRSSXML()
{
    Shop::Container()->getLogService()->debug('RSS wird erstellt');
    if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
        Shop::Container()->getLogService()->error(
            'RSS Verzeichnis ' . PFAD_ROOT . FILE_RSS_FEED . 'nicht beschreibbar!'
        );

        return false;
    }
    $shopURL = Shop::getURL();
    $db      = Shop::Container()->getDB();
    $conf    = Shop::getSettings([CONF_RSS]);
    if ($conf['rss']['rss_nutzen'] !== 'Y') {
        return false;
    }
    $language                = $db->select('tsprache', 'cShopStandard', 'Y');
    $stdKundengruppe         = $db->select('tkundengruppe', 'cStandard', 'Y');
    $_SESSION['kSprache']    = (int)$language->kSprache;
    $_SESSION['cISOSprache'] = $language->cISO;
    // ISO-8859-1
    $xml = '<?xml version="1.0" encoding="' . JTL_CHARSET . '"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>' . $conf['rss']['rss_titel'] . '</title>
		<link>' . $shopURL . '</link>
		<description>' . $conf['rss']['rss_description'] . '</description>
		<language>' . Text::convertISO2ISO639($language->cISO) . '</language>
		<copyright>' . $conf['rss']['rss_copyright'] . '</copyright>
		<pubDate>' . date('r') . '</pubDate>
		<atom:link href="' . $shopURL . '/rss.xml" rel="self" type="application/rss+xml" />
		<image>
			<url>' . $conf['rss']['rss_logoURL'] . '</url>
			<title>' . $conf['rss']['rss_titel'] . '</title>
			<link>' . $shopURL . '</link>
		</image>';
    //Artikel STD Sprache
    $lagerfilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
    $days        = (int)$conf['rss']['rss_alterTage'];
    if (!$days) {
        $days = 14;
    }
    // Artikel beachten?
    if ($conf['rss']['rss_artikel_beachten'] === 'Y') {
        $products = $db->query(
            "SELECT tartikel.kArtikel, tartikel.cName, tartikel.cKurzBeschreibung, tseo.cSeo, 
                tartikel.dLetzteAktualisierung, tartikel.dErstellt, 
                DATE_FORMAT(tartikel.dErstellt, '%a, %d %b %Y %H:%i:%s UTC') AS erstellt
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $stdKundengruppe->kKundengruppe . "
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cNeu = 'Y' " .  $lagerfilter . "
                    AND cNeu = 'Y' 
                    AND DATE_SUB(now(), INTERVAL " . $days . ' DAY) < dErstellt
                ORDER BY dLetzteAktualisierung DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($products as $product) {
            $url  = URL::buildURL($product, URLART_ARTIKEL, true);
            $xml .= '
        <item>
            <title>' . wandelXMLEntitiesUm($product->cName) . '</title>
            <description>' . wandelXMLEntitiesUm($product->cKurzBeschreibung) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($product->dLetzteAktualisierung) . '</pubDate>
        </item>';
        }
    }
    // News beachten?
    if ($conf['rss']['rss_news_beachten'] === 'Y') {
        $news = $db->query(
            "SELECT tnews.*, t.title, t.preview, DATE_FORMAT(dGueltigVon, '%a, %d %b %Y %H:%i:%s UTC') AS dErstellt_RSS
                FROM tnews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                WHERE DATE_SUB(now(), INTERVAL " . $days . ' DAY) < dGueltigVon
                    AND nAktiv = 1
                    AND dGueltigVon <= now()
                ORDER BY dGueltigVon DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($news as $item) {
            $url  = URL::buildURL($item, URLART_NEWS);
            $xml .= '
        <item>p
            <title>' . wandelXMLEntitiesUm($item->title) . '</title>
            <description>' . wandelXMLEntitiesUm($item->preview) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($item->dGueltigVon) . '</pubDate>
        </item>';
        }
    }
    // bewertungen beachten?
    if ($conf['rss']['rss_bewertungen_beachten'] === 'Y') {
        $reviews = $db->query(
            "SELECT *, dDatum, DATE_FORMAT(dDatum, '%a, %d %b %y %h:%i:%s +0100') AS dErstellt_RSS
                FROM tbewertung
                WHERE DATE_SUB(NOW(), INTERVAL " . $days . ' DAY) < dDatum
                    AND nAktiv = 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($reviews as $review) {
            $url  = URL::buildURL($review, URLART_ARTIKEL, true);
            $xml .= '
        <item>
            <title>Bewertung ' . wandelXMLEntitiesUm($review->cTitel) . ' von ' .
                wandelXMLEntitiesUm($review->cName) . '</title>
            <description>' . wandelXMLEntitiesUm($review->cText) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($review->dDatum) . '</pubDate>
        </item>';
        }
    }

    $xml .= '
	</channel>
</rss>
		';

    $file = fopen(PFAD_ROOT . FILE_RSS_FEED, 'w+');
    fwrite($file, $xml);
    fclose($file);

    return true;
}

/**
 * @param string $dateString
 * @return bool|string
 */
function bauerfc2822datum($dateString)
{
    return mb_strlen($dateString) > 0
        ? (new DateTime($dateString))->format(DATE_RSS)
        : false;
}

/**
 * @param string $text
 * @return string
 */
function wandelXMLEntitiesUm($text)
{
    return mb_strlen($text) > 0
        ? '<![CDATA[ ' . Text::htmlentitydecode($text) . ' ]]>'
        : '';
}
