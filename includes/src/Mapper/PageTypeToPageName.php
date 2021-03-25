<?php declare(strict_types=1);

namespace JTL\Mapper;

/**
 * Class PageTypeToPageName
 * @package JTL\Mapper
 */
class PageTypeToPageName
{
    /**
     * @param int $type
     * @return string
     */
    public function map(int $type): string
    {
        switch ($type) {
            case \PAGE_STARTSEITE:
            case \PAGE_VERSAND:
            case \PAGE_WRB:
            case \PAGE_AGB:
            case \PAGE_LIVESUCHE:
            case \PAGE_DATENSCHUTZ:
            case \PAGE_HERSTELLER:
            case \PAGE_SITEMAP:
            case \PAGE_GRATISGESCHENK:
            case \PAGE_AUSWAHLASSISTENT:
            case \PAGE_EIGENE:
                return 'SEITE';
            case \PAGE_MEINKONTO:
            case \PAGE_LOGIN:
                return 'MEIN KONTO';
            case \PAGE_REGISTRIERUNG:
                return 'REGISTRIEREN';
            case \PAGE_WARENKORB:
                return 'Warenkorb';
            case \PAGE_PASSWORTVERGESSEN:
                return 'PASSWORT VERGESSEN';
            case \PAGE_KONTAKT:
                return 'KONTAKT';
            case \PAGE_NEWSLETTER:
            case \PAGE_NEWSLETTERARCHIV:
                return 'NEWSLETTER';
            case \PAGE_NEWS:
                return 'News';
            case \PAGE_NEWSMONAT:
                return 'NEWSMONAT';
            case \PAGE_NEWSKATEGORIE:
                return 'NEWSKATEGORIE';
            case \PAGE_NEWSDETAIL:
                return 'NEWSDETAIL';
            case \PAGE_PLUGIN:
                return 'PLUGIN';
            case \PAGE_404:
                return '404';
            case \PAGE_BESTELLVORGANG:
            case \PAGE_BESTELLABSCHLUSS:
                return 'BESTELLVORGANG';
            case \PAGE_WUNSCHLISTE:
                return 'Wunschliste';
            case \PAGE_VERGLEICHSLISTE:
                return 'VERGLEICHSLISTE';
            case \PAGE_ARTIKEL:
            case \PAGE_ARTIKELLISTE:
                return 'Artikel';
            default:
                return '';
        }
    }
}
