<?php

use JTL\Catalog\Category\KategorieListe;
use JTL\Helpers\Category;
use JTL\Helpers\CMS;
use JTL\Link\LinkInterface;
use JTL\Shop;
use JTL\Sitemap\Sitemap;

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibStartBoxen()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getHomeBoxes();
}

/**
 * @param array $conf
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibNews($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getHomeNews($conf);
}

/**
 * @return null
 * @deprecated since 5.0.0
 */
function gibNextBoxPrio()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibLivesucheTop($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getLiveSearchTop($conf);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibLivesucheLast($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getLiveSearchLast($conf);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibTagging($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibNewsletterHistory()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getNewsletterHistory();
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibGratisGeschenkArtikel($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMS::getFreeGifts($conf);
}

/**
 * @return null
 * @deprecated since 5.0.0
 */
function gibAuswahlAssistentFragen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
    return null;
}

/**
 * @return KategorieListe
 * @deprecated since 5.0.0
 */
function gibSitemapKategorien()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $categoryList           = new KategorieListe();
    $categoryList->elemente = Category::getInstance()->combinedGetAll();

    return $categoryList;
}

/**
 * @deprecated since 5.0.0
 */
function gibSitemapGlobaleMerkmale()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalBild()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalWertBild()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibBoxNews()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return anything useful.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSitemapNews()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_NEWS]));

    return $sm->getNews();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibNewsKategorie()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_SITEMAP]));

    return $sm->getNewsCategories();
}

/**
 * @param array                 $conf
 * @param \JTL\Smarty\JTLSmarty $smarty
 * @deprecated since 5.0.0
 */
function gibSeiteSitemap($conf, $smarty)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::setPageType(PAGE_SITEMAP);
    $sm = new Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $conf);
    $sm->assignData($smarty);
}

/**
 * @param int $nLinkart
 * @deprecated since 5.0.0
 */
function pruefeSpezialseite(int $nLinkart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $specialPages = Shop::Container()->getLinkService()->getLinkGroupByName('specialpages');
    if ($nLinkart > 0 && $specialPages !== null) {
        $res = $specialPages->getLinks()->first(static function (LinkInterface $l) use ($nLinkart) {
            return $l->getLinkType() === $nLinkart;
        });
        /** @var LinkInterface $res */
        if ($res !== null && $res->getFileName() !== '') {
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute($res->getFileName()));
            exit();
        }
    }
}
