<?php declare(strict_types=1);

namespace JTL\Mapper;

/**
 * Class LinkTypeToPageType
 * @package JTL\Mapper
 */
class LinkTypeToPageType
{
    /**
     * @param int $type
     * @return int
     */
    public function map(int $type): int
    {
        switch ($type) {
            case \LINKTYP_EIGENER_CONTENT:
            case \LINKTYP_IMPRESSUM:
            case \LINKTYP_BATTERIEGESETZ_HINWEISE:
                return \PAGE_EIGENE;
            case \LINKTYP_STARTSEITE:
                return \PAGE_STARTSEITE;
            case \LINKTYP_VERSAND:
                return \PAGE_VERSAND;
            case \LINKTYP_LOGIN:
                return \PAGE_LOGIN;
            case \LINKTYP_REGISTRIEREN:
                return \PAGE_REGISTRIERUNG;
            case \LINKTYP_WARENKORB:
                return \PAGE_WARENKORB;
            case \LINKTYP_PASSWORD_VERGESSEN:
                return \PAGE_PASSWORTVERGESSEN;
            case \LINKTYP_AGB:
                return \PAGE_AGB;
            case \LINKTYP_DATENSCHUTZ:
                return \PAGE_DATENSCHUTZ;
            case \LINKTYP_KONTAKT:
                return \PAGE_KONTAKT;
            case \LINKTYP_LIVESUCHE:
                return \PAGE_LIVESUCHE;
            case \LINKTYP_HERSTELLER:
                return \PAGE_HERSTELLER;
            case \LINKTYP_NEWSLETTER:
                return \PAGE_NEWSLETTER;
            case \LINKTYP_NEWSLETTERARCHIV:
                return \PAGE_NEWSLETTERARCHIV;
            case \LINKTYP_NEWS:
                return \PAGE_NEWS;
            case \LINKTYP_SITEMAP:
                return \PAGE_SITEMAP;
            case \LINKTYP_GRATISGESCHENK:
                return \PAGE_GRATISGESCHENK;
            case \LINKTYP_WRB:
            case \LINKTYP_WRB_FORMULAR:
                return \PAGE_WRB;
            case \LINKTYP_PLUGIN:
                return \PAGE_PLUGIN;
            case \LINKTYP_AUSWAHLASSISTENT:
                return \PAGE_AUSWAHLASSISTENT;
            case \LINKTYP_404:
                return \PAGE_404;
            case \LINKTYP_BESTELLVORGANG:
                return \PAGE_BESTELLVORGANG;
            case \LINKTYP_BESTELLABSCHLUSS:
                return \PAGE_BESTELLABSCHLUSS;
            case \LINKTYP_WUNSCHLISTE:
                return \PAGE_WUNSCHLISTE;
            case \LINKTYP_VERGLEICHSLISTE:
                return \PAGE_VERGLEICHSLISTE;
            case \LINKTYP_EXTERNE_URL:
            default:
                return \PAGE_UNBEKANNT;
        }
    }
}
