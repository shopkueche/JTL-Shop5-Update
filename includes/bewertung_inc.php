<?php

/**
 * Fügt für einen bestimmten Artikel, in einer bestimmten Sprache eine Bewertung hinzu.
 *
 * @return string
 * @deprecated since 5.0.0
 */
function speicherBewertung(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * Speichert für eine bestimmte Bewertung und bestimmten Kunden ab, ob sie hilfreich oder nicht hilfreich war.
 *
 * @deprecated since 5.0.0s
 */
function speicherHilfreich()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function aktualisiereDurchschnitt(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return true;
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function pruefeKundeArtikelBewertet(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeKundeArtikelGekauft(): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return true;
}

/**
 * @return float
 * @deprecated since 5.0.0
 */
function checkeBewertungGuthabenBonus()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return 0.0;
}
