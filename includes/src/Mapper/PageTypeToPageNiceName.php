<?php declare(strict_types=1);

namespace JTL\Mapper;

/**
 * Class PageTypeToPageNiceName
 * @package JTL\Mapper
 */
class PageTypeToPageNiceName
{
    /**
     * @param int $type
     * @return string
     */
    public function mapPageTypeToPageNiceName(int $type): string
    {
        switch ($type) {
            case \PAGE_STARTSEITE:
                return __('Startseite');
            case \PAGE_VERSAND:
                return __('Informationen zum Versand');
            case \PAGE_WRB:
                return __('WRB');
            case \PAGE_AGB:
                return __('AGB');
            case \PAGE_LIVESUCHE:
                return __('Livesuche');
            case \PAGE_DATENSCHUTZ:
                return __('Datenschutz');
            case \PAGE_HERSTELLER:
                return __('Hersteller Übersicht');
            case \PAGE_SITEMAP:
                return __('Sitemap');
            case \PAGE_GRATISGESCHENK:
                return __('Gratis Geschenk');
            case \PAGE_AUSWAHLASSISTENT:
                return __('Auswahlassistent');
            case \PAGE_EIGENE:
                return __('pageCustom');
            case \PAGE_MEINKONTO:
                return __('pageAccount');
            case \PAGE_LOGIN:
                return __('Login');
            case \PAGE_REGISTRIERUNG:
                return __('Registrieren');
            case \PAGE_WARENKORB:
                return __('Warenkorb');
            case \PAGE_PASSWORTVERGESSEN:
                return __('Passwort vergessen');
            case \PAGE_KONTAKT:
                return __('Kontakt');
            case \PAGE_NEWSLETTER:
                return __('Newsletter');
            case \PAGE_NEWSLETTERARCHIV:
                return __('Newsletterarchiv');
            case \PAGE_NEWS:
                return __('News');
            case \PAGE_NEWSMONAT:
                return __('pageNewsMonth');
            case \PAGE_NEWSKATEGORIE:
                return __('pageNewsCategory');
            case \PAGE_NEWSDETAIL:
                return __('pageNewsDetail');
            case \PAGE_PLUGIN:
                return __('pagePlugin');
            case \PAGE_404:
                return __('404');
            case \PAGE_BESTELLVORGANG:
                return __('Bestellvorgang');
            case \PAGE_BESTELLABSCHLUSS:
                return __('Bestellabschluss');
            case \PAGE_WUNSCHLISTE:
                return __('Wunschliste');
            case \PAGE_VERGLEICHSLISTE:
                return __('Vergleichsliste');
            case \PAGE_ARTIKEL:
                return __('pageProduct');
            case \PAGE_ARTIKELLISTE:
                return __('pageProductList');
            case \PAGE_BEWERTUNG:
                return __('pageRating');
            case \PAGE_WARTUNG:
                return __('pageMaintenance');
            case \PAGE_BESTELLSTATUS:
                return __('pageOrderStatus');
            case \PAGE_UNBEKANNT:
                return __('pageUnknown');
            default:
                return '';
        }
    }
}
