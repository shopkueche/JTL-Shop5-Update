<?php

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function gibRedirect()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * Schaut nach dem Login, ob Kategorien nicht sichtbar sein dürfen und löscht eventuell diese aus der Session
 *
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeKategorieSichtbarkeit()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return true;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeWarenkorbPersInWarenkorb(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
 *
 * @deprecated since 5.0.0
 */
function pruefeWarenkorbArtikelSichtbarkeit(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.0.0
 */
function fuehreLoginAus(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}
