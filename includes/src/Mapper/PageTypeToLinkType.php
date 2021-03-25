<?php declare(strict_types=1);

namespace JTL\Mapper;

/**
 * Class PageTypeToLinkType
 * @package JTL\Mapper
 */
class PageTypeToLinkType
{
    /**
     * @param int $type
     * @return int
     */
    public function map(int $type): int
    {
        switch ($type) {
            case \PAGE_EIGENE:
                return \LINKTYP_EIGENER_CONTENT;
            case \PAGE_STARTSEITE:
                return \LINKTYP_STARTSEITE;
            case \PAGE_VERSAND:
                return \LINKTYP_VERSAND;
            case \PAGE_LOGIN:
            case \PAGE_MEINKONTO:
                return \LINKTYP_LOGIN;
            case \PAGE_REGISTRIERUNG:
                return \LINKTYP_REGISTRIEREN;
            case \PAGE_WARENKORB:
                return \LINKTYP_WARENKORB;
            case \PAGE_PASSWORTVERGESSEN:
                return \LINKTYP_PASSWORD_VERGESSEN;
            case \PAGE_AGB:
                return \LINKTYP_AGB;
            case \PAGE_DATENSCHUTZ:
                return \LINKTYP_DATENSCHUTZ;
            case \PAGE_KONTAKT:
                return \LINKTYP_KONTAKT;
            case \PAGE_LIVESUCHE:
                return \LINKTYP_LIVESUCHE;
            case \PAGE_HERSTELLER:
                return \LINKTYP_HERSTELLER;
            case \PAGE_NEWSLETTER:
                return \LINKTYP_NEWSLETTER;
            case \PAGE_NEWSLETTERARCHIV:
                return \LINKTYP_NEWSLETTERARCHIV;
            case \PAGE_NEWS:
            case \PAGE_NEWSDETAIL:
            case \PAGE_NEWSKATEGORIE:
            case \PAGE_NEWSMONAT:
                return \LINKTYP_NEWS;
            case \PAGE_SITEMAP:
                return \LINKTYP_SITEMAP;
            case \PAGE_GRATISGESCHENK:
                return \LINKTYP_GRATISGESCHENK;
            case \PAGE_WRB:
                return \LINKTYP_WRB;
            case \PAGE_PLUGIN:
                return \LINKTYP_PLUGIN;
            case \PAGE_AUSWAHLASSISTENT:
                return \LINKTYP_AUSWAHLASSISTENT;
            case \PAGE_404:
                return \LINKTYP_404;
            case \PAGE_BESTELLVORGANG:
                return \LINKTYP_BESTELLVORGANG;
            case \PAGE_BESTELLABSCHLUSS:
                return \LINKTYP_BESTELLABSCHLUSS;
            case \PAGE_WUNSCHLISTE:
                return \LINKTYP_WUNSCHLISTE;
            case \PAGE_VERGLEICHSLISTE:
                return \LINKTYP_VERGLEICHSLISTE;
            default:
                return 0;
        }
    }
}
