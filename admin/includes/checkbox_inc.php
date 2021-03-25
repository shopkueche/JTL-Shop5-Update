<?php

use JTL\CheckBox;
use JTL\Helpers\Text;
use JTL\Language\LanguageModel;
use JTL\Shop;

/**
 * @param array $post
 * @param LanguageModel[] $languages
 * @return array
 */
function plausiCheckBox(array $post, array $languages): array
{
    $checks = [];
    if (!is_array($languages) || count($languages) === 0) {
        $checks['oSprache_arr'] = 1;

        return $checks;
    }
    if (!isset($post['cName']) || mb_strlen($post['cName']) === 0) {
        $checks['cName'] = 1;
    }
    $text = false;
    $link = true;
    foreach ($languages as $language) {
        if (mb_strlen($post['cText_' . $language->getIso()]) > 0) {
            $text = true;
            break;
        }
    }
    if (!$text) {
        $checks['cText'] = 1;
    }
    if ((int)$post['nLink'] === 1) {
        $link = isset($post['kLink']) && (int)$post['kLink'] > 0;
    }
    if (!$link) {
        $checks['kLink'] = 1;
    }
    if (!is_array($post['cAnzeigeOrt']) || count($post['cAnzeigeOrt']) === 0) {
        $checks['cAnzeigeOrt'] = 1;
    } else {
        foreach ($post['cAnzeigeOrt'] as $cAnzeigeOrt) {
            if ((int)$cAnzeigeOrt === 3 && (int)$post['kCheckBoxFunktion'] === 1) {
                $checks['cAnzeigeOrt'] = 2;
            }
        }
    }
    if (!isset($post['nPflicht']) || mb_strlen($post['nPflicht']) === 0) {
        $checks['nPflicht'] = 1;
    }
    if (!isset($post['nAktiv']) || mb_strlen($post['nAktiv']) === 0) {
        $checks['nAktiv'] = 1;
    }
    if (!isset($post['nLogging']) || mb_strlen($post['nLogging']) === 0) {
        $checks['nLogging'] = 1;
    }
    if (!isset($post['nSort']) || (int)$post['nSort'] === 0) {
        $checks['nSort'] = 1;
    }
    if (!is_array($post['kKundengruppe']) || count($post['kKundengruppe']) === 0) {
        $checks['kKundengruppe'] = 1;
    }

    return $checks;
}

/**
 * @param array $post
 * @param LanguageModel[] $languages
 * @return CheckBox
 */
function speicherCheckBox(array $post, array $languages): CheckBox
{
    if (isset($post['kCheckBox']) && (int)$post['kCheckBox'] > 0) {
        $checkBox = new CheckBox((int)$post['kCheckBox']);
        $checkBox->deleteCheckBox([(int)$post['kCheckBox']]);
    } else {
        $checkBox = new CheckBox();
    }
    $checkBox->kLink = 0;
    if ((int)$post['nLink'] === 1) {
        $checkBox->kLink = (int)$post['kLink'];
    }
    $checkBox->kCheckBoxFunktion = (int)$post['kCheckBoxFunktion'];
    $checkBox->cName             = htmlspecialchars($post['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $checkBox->cKundengruppe     = Text::createSSK($post['kKundengruppe']);
    $checkBox->cAnzeigeOrt       = Text::createSSK($post['cAnzeigeOrt']);
    $checkBox->nAktiv            = 0;
    if ($post['nAktiv'] === 'Y') {
        $checkBox->nAktiv = 1;
    }
    $checkBox->nPflicht = 0;
    $checkBox->nLogging = 0;
    if ($post['nLogging'] === 'Y') {
        $checkBox->nLogging = 1;
    }
    if ($post['nPflicht'] === 'Y') {
        $checkBox->nPflicht = 1;
    }
    $checkBox->nSort     = (int)$post['nSort'];
    $checkBox->dErstellt = 'NOW()';
    $texts               = [];
    $descr               = [];
    foreach ($languages as $language) {
        $code         = $language->getIso();
        $texts[$code] = str_replace('"', '&quot;', $post['cText_' . $code]);
        $descr[$code] = str_replace('"', '&quot;', $post['cBeschreibung_' . $code]);
    }

    $checkBox->insertDB($texts, $descr);
    Shop::Container()->getCache()->flushTags(['checkbox']);

    return $checkBox;
}
