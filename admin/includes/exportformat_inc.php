<?php

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * @deprecated since 4.05
 * @param array $splits
 * @param int   $fileCounter
 * @return string
 */
function gibDateiname($splits, $fileCounter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_array($splits) && count($splits) > 1) {
        return $splits[0] . $fileCounter . $splits[1];
    }

    return $splits[0] . $fileCounter;
}

/**
 * @deprecated since 4.05
 * @param array $splits
 * @param int   $fileCounter
 * @return string
 */
function gibDateiPfad($splits, $fileCounter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return PFAD_ROOT . PFAD_EXPORT . gibDateiname($splits, $fileCounter);
}

/**
 * @deprecated since 4.05
 * @return array
 */
function pruefeExportformat()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $checks = [];
    // Name
    if (mb_strlen(Request::postVar('cName', '')) === 0) {
        $checks['cName'] = 1;
    }
    // Dateiname
    if (mb_strlen(Request::postVar('cDateiname', '')) === 0) {
        $checks['cDateiname'] = 1;
    }
    // Dateiname Endung fehlt
    if (mb_strpos(Request::postVar('cDateiname', ''), '.') === false) {
        $checks['cDateiname'] = 2;
    }
    if (mb_strlen(Request::postVar('cContent', '')) === 0) {
        $checks['cContent'] = 1;
    }
    if (Request::postInt('kSprache') === 0) {
        $checks['kSprache'] = 1;
    }
    if (Request::postInt('kWaehrung') === 0) {
        $checks['kWaehrung'] = 1;
    }
    if (Request::postInt('kKundengruppe') === 0) {
        $checks['kKundengruppe'] = 1;
    }

    return $checks;
}

/**
 * Falls eingestellt, wird die Exportdatei in mehrere Dateien gesplittet
 *
 * @deprecated since 4.05
 * @param object $export
 */
function splitteExportDatei($export)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (isset($export->nSplitgroesse)
        && (int)$export->nSplitgroesse > 0
        && file_exists(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname)
    ) {
        $fileCounter = 1;
        $splits      = [];
        $extIndex    = mb_strrpos($export->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($extIndex === false) {
            $splits[0] = $export->cDateiname;
        } else {
            $splits[0] = mb_substr($export->cDateiname, 0, $extIndex);
            $splits[1] = mb_substr($export->cDateiname, $extIndex);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname) >=
            ($export->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            loescheExportDateien($export->cDateiname, $splits[0]);
            $handle    = fopen(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname, 'r');
            $row       = 1;
            $newHandle = fopen(gibDateiPfad($splits, $fileCounter), 'w');
            $fileSize  = 0;
            while (($cContent = fgets($handle)) !== false) {
                if ($row > 1) {
                    $rowSize = mb_strlen($cContent) + 2;
                    //Schwelle erreicht?
                    if ($fileSize <= ($export->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($newHandle, $cContent);
                        $fileSize += $rowSize;
                    } else {
                        //neue Datei
                        schreibeFusszeile($newHandle, $export->cFusszeile, $export->cKodierung);
                        fclose($newHandle);
                        $fileCounter++;
                        $newHandle = fopen(gibDateiPfad($splits, $fileCounter), 'w');
                        schreibeKopfzeile($newHandle, $export->cKopfzeile, $export->cKodierung);
                        // Schreibe Content
                        fwrite($newHandle, $cContent);
                        $fileSize = $rowSize;
                    }
                } elseif ($row === 1) {
                    schreibeKopfzeile($newHandle, $export->cKopfzeile, $export->cKodierung);
                }
                $row++;
            }
            fclose($newHandle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $export->cDateiname);
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cKopfzeile
 * @param string   $cKodierung
 */
function schreibeKopfzeile($dateiHandle, $cKopfzeile, $cKodierung)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($cKopfzeile) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            if ($cKodierung === 'UTF-8') {
                fwrite($dateiHandle, "\xEF\xBB\xBF");
            }
            fwrite($dateiHandle, $cKopfzeile . "\n");
        } else {
            fwrite($dateiHandle, Text::convertISO($cKopfzeile . "\n"));
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cFusszeile
 * @param string   $cKodierung
 */
function schreibeFusszeile($dateiHandle, $cFusszeile, $cKodierung)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (mb_strlen($cFusszeile) > 0) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            fwrite($dateiHandle, $cFusszeile);
        } else {
            fwrite($dateiHandle, Text::convertISO($cFusszeile));
        }
    }
}

/**
 * @deprecated since 4.05
 * @param string $cDateiname
 * @param string $cDateinameSplit
 */
function loescheExportDateien($cDateiname, $cDateinameSplit)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_dir(PFAD_ROOT . PFAD_EXPORT)) {
        $dir = opendir(PFAD_ROOT . PFAD_EXPORT);
        if ($dir !== false) {
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== $cDateiname && mb_strpos($cDatei, $cDateinameSplit) !== false) {
                    @unlink(PFAD_ROOT . PFAD_EXPORT . $cDatei);
                }
            }
            closedir($dir);
        }
    }
}

/**
 * @param int $kExportformat
 * @return array
 * @deprecated since 4.05
 */
function getEinstellungenExport(int $kExportformat)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $ret = [];
    if ($kExportformat > 0) {
        $conf = Shop::Container()->getDB()->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $kExportformat,
            'cName, cWert'
        );
        foreach ($conf as $eins) {
            if ($eins->cName) {
                $ret[$eins->cName] = $eins->cWert;
            }
        }
    }

    return $ret;
}

/**
 * @deprecated since 4.05
 * @param object $export
 * @return array
 */
function baueArtikelExportSQL($export)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sql          = [];
    $sql['Where'] = '';
    $sql['Join']  = '';

    if (empty($export->kExportformat)) {
        return $sql;
    }
    $conf = getEinstellungenExport((int)$export->kExportformat);

    switch ($export->nVarKombiOption) {
        case 2:
            $sql['Where'] = ' AND kVaterArtikel = 0';
            break;
        case 3:
            $sql['Where'] = ' AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)';
            break;
    }
    if (isset($conf['exportformate_lager_ueber_null']) && $conf['exportformate_lager_ueber_null'] === 'Y') {
        $sql['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
    } elseif (isset($conf['exportformate_lager_ueber_null']) && $conf['exportformate_lager_ueber_null'] === 'O') {
        $sql['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                                    OR tartikel.cLagerKleinerNull = 'Y')";
    }

    if (isset($conf['exportformate_preis_ueber_null']) && $conf['exportformate_preis_ueber_null'] === 'Y') {
        $sql['Join'] .= ' JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                                AND tpreis.kKundengruppe = ' . (int)$export->kKundengruppe . '
                          JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                                AND tpreisdetail.nAnzahlAb = 0
                                AND tpreisdetail.fVKNetto > 0';
    }

    if (isset($conf['exportformate_beschreibung']) && $conf['exportformate_beschreibung'] === 'Y'
    ) {
        $sql['Where'] .= " AND tartikel.cBeschreibung != ''";
    }

    return $sql;
}

/**
 * @deprecated since 4.05
 * @param object $export
 * @return mixed
 */
function holeMaxExportArtikelAnzahl(&$export)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $data = baueArtikelExportSQL($export);
    $conf = Shop::getSettings([CONF_GLOBAL]);
    $sql  = 'AND tartikel.dErscheinungsdatum IS NULL OR (DATE(tartikel.dErscheinungsdatum) <= CURDATE())';
    if (isset($conf['global']['global_erscheinende_kaeuflich'])
        && $conf['global']['global_erscheinende_kaeuflich'] === 'Y'
    ) {
        $sql = "AND (
                    tartikel.dErscheinungsdatum IS NULL 
                    OR (DATE(tartikel.dErscheinungsdatum) <= CURDATE())
                    OR (
                        DATE(tartikel.dErscheinungsdatum) > CURDATE()
                        AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = 'Y')
                    )
                )";
    }
    $cid = 'xp_' . md5(json_encode($data) . $sql);
    if (($count = Shop::Container()->getCache()->get($cid)) !== false) {
        return $count;
    }
    $count = Shop::Container()->getDB()->query(
        "SELECT COUNT(*) AS nAnzahl
            FROM tartikel
            LEFT JOIN tartikelattribut 
                ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$export->kKundengruppe . '
            ' . $data['Join'] . '
            WHERE tartikelattribut.kArtikelAttribut IS NULL' . $data['Where'] . '
                AND tartikelsichtbarkeit.kArtikel IS NULL ' . $sql,
        ReturnType::SINGLE_OBJECT
    );
    Shop::Container()->getCache()->set($cid, $count, [CACHING_GROUP_CORE], 120);

    return $count;
}
