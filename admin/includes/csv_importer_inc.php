<?php

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * If the "Import CSV" button was clicked with the id $importerId, try to insert entries from the CSV file uploaded
 * into to the table $target or call a function for each row to be imported. Call this function before you read the
 * data from the table again! Make sure, the CSV contains all important fields to form a valid row in your DB-table!
 * Missing fields in the CSV will be set to the DB-tables default value if your DB is configured so.
 *
 * @param string $importerId
 * @param string|callable $target - either target table name or callback function that takes an object to be imported
 * @param string[] $fields - array of names of the fields in the order they appear in one data row. If and only if this
 *      array is empty, a header line of field names is expected, otherwise not.
 * @param string|null $delim - delimiter character or null to guess it from the first row
 * @param int $importType -
 *      0 = clear table, then import (careful!!! again: this will clear the table denoted by $target)
 *      1 = insert new, overwrite existing
 *      2 = insert only non-existing
 * @param string[] - output a list of error messages
 * @return int - -1 if importer-id-mismatch / 0 on success / >1 import error count
 * @throws TypeError
 * @throws InvalidArgumentException
 */
function handleCsvImportAction(
    string $importerId,
    $target,
    array $fields = [],
    $delim = null,
    int $importType = 2,
    &$errors = []
) {
    if (Form::validateToken() === false || Request::verifyGPDataString('importcsv') !== $importerId) {
        return -1;
    }

    if (!is_string($target) && !is_callable($target)) {
        throw new TypeError('argument $target must be either a string or a callable');
    }

    if (isset($_REQUEST['importType'])) {
        $importType = Request::verifyGPCDataInt('importType');
    }

    if ($importType !== 0 && $importType !== 1 && $importType !== 2) {
        throw new InvalidArgumentException('$importType must be 0, 1 or 2');
    }

    $csvMime = $_FILES['csvfile']['type'] ?? null;

    if ($csvMime !== 'application/vnd.ms-excel'
        && $csvMime !== 'text/csv'
        && $csvMime !== 'application/csv'
        && $csvMime !== 'application/vnd.msexcel'
    ) {
        $errors[] = __('csvImportInvalidMime');
        return 1;
    }

    $csvFilename       = $_FILES['csvfile']['tmp_name'];
    $fs                = fopen($_FILES['csvfile']['tmp_name'], 'rb');
    $nErrors           = 0;
    $importDeleteDone  = false;
    $oldRedirectFormat = false;
    $defLanguage       = LanguageHelper::getDefaultLanguage();
    $rowIndex          = 2;

    if ($delim === null) {
        $delim = getCsvDelimiter($csvFilename);
    }

    if (count($fields) === 0) {
        $fields = fgetcsv($fs, 0, $delim);
    }

    $articleNumberPresent = false;
    $destUrlPresent       = false;

    foreach ($fields as &$field) {
        if ($field === 'sourceurl') {
            $field             = 'cFromUrl';
            $oldRedirectFormat = true;
        } elseif ($field === 'destinationurl') {
            $field             = 'cToUrl';
            $oldRedirectFormat = true;
            $destUrlPresent    = true;
        } elseif ($field === 'articlenumber') {
            $field                = 'cArtNr';
            $oldRedirectFormat    = true;
            $articleNumberPresent = true;
        } elseif ($field === 'languageiso') {
            $field             = 'cIso';
            $oldRedirectFormat = true;
        }
    }

    unset($field);

    if ($oldRedirectFormat) {
        if ($destUrlPresent === false && $articleNumberPresent === false) {
            $errors[] = __('csvImportNoArtNrOrDestUrl');
            return 1;
        }

        if ($destUrlPresent === true && $articleNumberPresent === true) {
            $errors[] = __('csvImportArtNrAndDestUrlError');
            return 1;
        }
    }

    if ($importType === 0 && is_string($target)) {
        Shop::Container()->getDB()->query('TRUNCATE ' . $target, ReturnType::AFFECTED_ROWS);
    }

    while (($row = fgetcsv($fs, 0, $delim)) !== false) {
        $obj = new stdClass();

        foreach ($fields as $i => $field) {
            $obj->$field = Shop::Container()->getDB()->escape($row[$i]);
        }

        if ($oldRedirectFormat) {
            $parsed = parse_url($obj->cFromUrl);
            $from   = $parsed['path'];

            if (isset($parsed['query'])) {
                $from .= '?' . $parsed['query'];
            }

            $obj->cFromUrl = $from;
        }

        if ($articleNumberPresent) {
            $obj->cToUrl = getArtNrUrl($obj->cArtNr, $obj->cIso ?? $defLanguage->cISO);

            if (empty($obj->cToUrl)) {
                ++$nErrors;
                $errors[] = sprintf(__('csvImportArtNrNotFound'), $obj->cArtNr);
                continue;
            }

            unset($obj->cArtNr, $obj->cIso);
        }

        if (is_callable($target)) {
            $res = $target($obj, $importDeleteDone, $importType);

            if ($res === false) {
                ++$nErrors;
            }
        } else { // is_string($target)
            $table = $target;

            if ($importType === 1) {
                Shop::Container()->getDB()->delete($target, $fields, $row);
            }

            $res = Shop::Container()->getDB()->insert($table, $obj);

            if ($res === 0) {
                ++$nErrors;
                $errors[] = sprintf(__('csvImportSaveError'), $rowIndex);
            }
        }

        ++$rowIndex;
    }

    return $nErrors;
}

/**
 * @param string $artNo
 * @param string $iso
 * @return string|null
 */
function getArtNrUrl(string $artNo, string $iso): ?string
{
    if ($artNo === '') {
        return null;
    }

    $item = Shop::Container()->getDB()->executeQueryPrepared(
        "SELECT tartikel.kArtikel, tseo.cSeo
            FROM tartikel
            LEFT JOIN tsprache
                ON tsprache.cISO = :iso
            LEFT JOIN tseo
                ON tseo.kKey = tartikel.kArtikel
                AND tseo.cKey = 'kArtikel'
                AND tseo.kSprache = tsprache.kSprache
            WHERE tartikel.cArtNr = :artno
            LIMIT 1",
        ['iso' => mb_convert_case($iso, MB_CASE_LOWER), 'artno' => $artNo],
        ReturnType::SINGLE_OBJECT
    );

    return URL::buildURL($item, URLART_ARTIKEL);
}
