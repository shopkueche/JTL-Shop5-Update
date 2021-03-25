<?php

namespace JTL\Update;

use Exception;
use JTL\DB\ReturnType;
use stdClass;

/**
 * Trait MigrationTableTrait
 * @package JTL\Update
 */
trait MigrationTableTrait
{
    /**
     * @return array
     */
    public function getLocaleSections(): array
    {
        $result = [];
        $items  = $this->fetchAll('SELECT kSprachsektion AS id, cName AS name FROM tsprachsektion');
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getLocales(): array
    {
        $result = [];
        $items  = $this->fetchAll('SELECT kSprachISO AS id, cISO AS name FROM tsprachiso');
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @param string $table
     * @param string $column
     */
    public function dropColumn($table, $column): void
    {
        try {
            $this->execute("ALTER TABLE `{$table}` DROP `{$column}`");
        } catch (Exception $e) {
        }
    }

    /**
     * Add or update a row in tsprachwerte
     *
     * @param string $locale  locale iso code e.g. "ger"
     * @param string $section section e.g. "global". See tsprachsektion for all sections
     * @param string $key     unique name to identify localization
     * @param string $value   localized text
     * @param bool   $system  optional flag for system-default.
     * @throws Exception if locale key or section is wrong
     */
    public function setLocalization($locale, $section, $key, $value, $system = true): void
    {
        $locales  = $this->getLocales();
        $sections = $this->getLocaleSections();

        if (!isset($locales[$locale])) {
            throw new Exception("Locale key '{$locale}' not found");
        }

        if (!isset($sections[$section])) {
            throw new Exception("section name '{$section}' not found");
        }

        $this->execute(
            "INSERT INTO tsprachwerte SET
            kSprachISO = '{$locales[$locale]}', 
            kSprachsektion = '{$sections[$section]}', 
            cName = '{$key}', 
            cWert = '{$value}', 
            cStandard = '{$value}', 
            bSystem = '{$system}' 
            ON DUPLICATE KEY UPDATE 
                cWert = IF(cWert = cStandard, VALUES(cStandard), cWert), cStandard = VALUES(cStandard)"
        );
    }

    /**
     * @param string $key
     * @param string|null $section
     */
    public function removeLocalization($key, $section = null): void
    {
        if ($section) {
            $this->getDB()->queryPrepared(
                'DELETE tsprachwerte
                    FROM tsprachwerte
                    INNER JOIN tsprachsektion USING(kSprachsektion)
                    WHERE tsprachwerte.cName = :langKey AND tsprachsektion.cName = :langSection',
                [
                    'langKey'     => $key,
                    'langSection' => $section
                ],
                ReturnType::DEFAULT
            );
        } else {
            $this->getDB()->queryPrepared(
                'DELETE FROM tsprachwerte WHERE cName = :langKey',
                ['langKey' => $key],
                ReturnType::DEFAULT
            );
        }
    }

    /**
     * @return array
     */
    private function getAvailableInputTypes(): array
    {
        return [
            'selectbox',
            'number',
            'pass',
            'text',
            'kommazahl',
            'listbox',
            'selectkdngrp',
            'color'
        ];
    }

    /**
     * @param string $table
     * @param string $column
     * @return mixed
     */
    private function getLastId($table, $column)
    {
        $result = $this->fetchOne(" SELECT `$column` as last_id FROM `$table` ORDER BY `$column` DESC LIMIT 1");

        return ++$result->last_id;
    }

    /**
     * @param string      $configName    internal config name
     * @param string      $configValue   default config value
     * @param int         $configSection config section
     * @param string      $externalName  displayed config name
     * @param string      $inputType     config input type (set to NULL and set additionalProperties->cConf to "N" for
     *                                   section header)
     * @param int         $sort          internal sorting number
     * @param object|null $additionalProperties
     * @param bool        $overwrite     force overwrite of already existing config
     * @throws Exception
     */
    public function setConfig(
        $configName,
        $configValue,
        $configSection,
        $externalName,
        $inputType,
        $sort,
        $additionalProperties = null,
        $overwrite = false
    ): void {
        $availableInputTypes = $this->getAvailableInputTypes();

        //input types that need $additionalProperties->inputOptions
        $inputTypeNeedsOptions = ['listbox', 'selectbox'];

        $kEinstellungenConf = (!\is_object($additionalProperties) ||
            !isset($additionalProperties->kEinstellungenConf) ||
            !$additionalProperties->kEinstellungenConf)
            ? $this->getLastId('teinstellungenconf', 'kEinstellungenConf')
            : $additionalProperties->kEinstellungenConf;
        if (!$configName) {
            throw new Exception('configName not provided or empty / zero');
        }
        if (!$configSection) {
            throw new Exception('configSection not provided or empty / zero');
        }
        if (!$externalName) {
            throw new Exception('externalName not provided or empty / zero');
        }
        if (!$sort) {
            throw new Exception('sort not provided or empty / zero');
        }
        if (!$inputType
            && (!\is_object($additionalProperties)
                || !isset($additionalProperties->cConf)
                || $additionalProperties->cConf !== 'N')
        ) {
            throw new Exception('inputType has to be provided if additionalProperties->cConf is not set to "N"');
        }
        if (\in_array($inputType, $inputTypeNeedsOptions, true)
            && (!\is_object($additionalProperties)
                || !isset($additionalProperties->inputOptions)
                || !\is_array($additionalProperties->inputOptions)
                || \count($additionalProperties->inputOptions) === 0)
        ) {
            throw new Exception('additionalProperties->inputOptions has to be provided if inputType is "' .
                $inputType . '"');
        }
        if ($overwrite !== true) {
            $count = $this->fetchOne(
                "SELECT COUNT(*) AS count 
                    FROM teinstellungen 
                    WHERE cName='{$configName}'"
            );
            if ((int)$count->count !== 0) {
                throw new Exception('another entry already present in teinstellungen and overwrite is disabled');
            }
            $count = $this->fetchOne(
                "SELECT COUNT(*) AS count 
                    FROM teinstellungenconf 
                    WHERE cWertName='{$configName}' 
                        OR kEinstellungenConf={$kEinstellungenConf}"
            );
            if ((int)$count->count !== 0) {
                throw new Exception('another entry already present in teinstellungenconf and overwrite is disabled');
            }
            $count = $this->fetchOne(
                "SELECT COUNT(*) AS count 
                    FROM teinstellungenconfwerte 
                    WHERE kEinstellungenConf={$kEinstellungenConf}"
            );
            if ((int)$count->count !== 0) {
                throw new Exception('another entry already present in ' .
                    'teinstellungenconfwerte and overwrite is disabled'
                );
            }

            unset($count);

            // $overwrite has to be set to true in order to create a new inputType
            if (!\in_array($inputType, $availableInputTypes, true)
                && (!\is_object($additionalProperties)
                    || !isset($additionalProperties->cConf)
                    || $additionalProperties->cConf !== 'N')
            ) {
                throw new Exception('inputType "' . $inputType .
                    '" not in available types and additionalProperties->cConf is not set to "N"');
            }
        }
        $this->removeConfig($configName);

        $cConf             = (!\is_object($additionalProperties)
            || !isset($additionalProperties->cConf)
            || $additionalProperties->cConf !== 'N')
            ? 'Y'
            : 'N';
        $inputType         = $cConf === 'N' ? '' : $inputType;
        $cModulId          = $additionalProperties->cModulId ?? '_DBNULL_';
        $cBeschreibung     = $additionalProperties->cBeschreibung ?? '';
        $nStandardAnzeigen = $additionalProperties->nStandardAnzeigen ?? 1;
        $nModul            = $additionalProperties->nModul ?? 0;

        $einstellungen                        = new stdClass();
        $einstellungen->kEinstellungenSektion = $configSection;
        $einstellungen->cName                 = $configName;
        $einstellungen->cWert                 = $configValue;
        $einstellungen->cModulId              = $cModulId;
        $this->getDB()->insert('teinstellungen', $einstellungen);
        unset($einstellungen);

        $einstellungenConf                        = new stdClass();
        $einstellungenConf->kEinstellungenConf    = $kEinstellungenConf;
        $einstellungenConf->kEinstellungenSektion = $configSection;
        $einstellungenConf->cName                 = $externalName;
        $einstellungenConf->cBeschreibung         = $cBeschreibung;
        $einstellungenConf->cWertName             = $configName;
        $einstellungenConf->cInputTyp             = $inputType;
        $einstellungenConf->cModulId              = $cModulId;
        $einstellungenConf->nSort                 = $sort;
        $einstellungenConf->nStandardAnzeigen     = $nStandardAnzeigen;
        $einstellungenConf->nModul                = $nModul;
        $einstellungenConf->cConf                 = $cConf;
        $this->getDB()->insert('teinstellungenconf', $einstellungenConf);
        unset($einstellungenConf);

        if (\is_object($additionalProperties)
            && isset($additionalProperties->inputOptions)
            && \is_array($additionalProperties->inputOptions)
        ) {
            $sortIndex              = 1;
            $einstellungenConfWerte = new stdClass();
            foreach ($additionalProperties->inputOptions as $optionKey => $optionValue) {
                $einstellungenConfWerte->kEinstellungenConf = $kEinstellungenConf;
                $einstellungenConfWerte->cName              = $optionValue;
                $einstellungenConfWerte->cWert              = $optionKey;
                $einstellungenConfWerte->nSort              = $sortIndex;
                $this->getDB()->insert('teinstellungenconfwerte', $einstellungenConfWerte);
                $sortIndex++;
            }
            unset($einstellungenConfWerte);
        }
    }

    /**
     * @param string $key the key name to be removed
     */
    public function removeConfig($key): void
    {
        $this->execute("DELETE FROM teinstellungen WHERE cName = '{$key}'");
        $this->execute(
            "DELETE FROM teinstellungenconfwerte 
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf 
                        FROM teinstellungenconf 
                        WHERE cWertName = '{$key}'
                )"
        );
        $this->execute("DELETE FROM teinstellungenconf WHERE cWertName = '{$key}'");
    }
}
