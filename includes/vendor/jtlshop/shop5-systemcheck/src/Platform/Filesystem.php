<?php declare(strict_types=1);

namespace Systemcheck\Platform;

use stdClass;

/**
 * Class Filesystem
 * @package Systemcheck\Platform
 */
class Filesystem
{
    /**
     * root path of this shop - optional
     *
     * @var string
     */
    protected $rootPath = '';

    /**
     * array of all shop folders
     *
     * @var array|null
     */
    protected $shopFolders;

    /**
     * array of strings (folder-names) with their states
     *
     * @var null|array
     */
    protected $foldersChecked;

    /**
     * result of the folder-check
     *
     * @var bool
     */
    protected $passed = true;

    /**
     * @var array
     */
    protected $writableEntities = [
    'bilder/news'
    , 'bilder/intern/shoplogo'
    , 'mediafiles/Bilder'
    , 'mediafiles/Musik'
    , 'mediafiles/Sonstiges'
    , 'mediafiles/Videos'
    , 'bilder/banner'
    , 'bilder/produkte/mini'
    , 'bilder/produkte/klein'
    , 'bilder/produkte/normal'
    , 'bilder/produkte/gross'
    , 'bilder/kategorien'
    , 'bilder/variationen/mini'
    , 'bilder/variationen/normal'
    , 'bilder/variationen/gross'
    , 'bilder/hersteller/normal'
    , 'bilder/hersteller/klein'
    , 'bilder/merkmale/normal'
    , 'bilder/merkmale/klein'
    , 'bilder/merkmalwerte/normal'
    , 'bilder/merkmalwerte/klein'
    , 'bilder/brandingbilder'
    , 'bilder/suchspecialoverlay/klein'
    , 'bilder/suchspecialoverlay/normal'
    , 'bilder/suchspecialoverlay/gross'
    , 'bilder/konfigurator/klein'
    , 'bilder/links'
    , 'bilder/newsletter'
    , 'jtllogs'
    , 'export'
    , 'export/backup'
    , 'export/yatego'
    , 'templates_c'
    , 'dbeS/tmp'
    , 'dbeS/logs'
    , 'uploads'
    , 'media/image'
    , 'media/image/storage'
    , 'media/image/category'
    , 'media/image/characteristic'
    , 'media/image/characteristicvalue'
    , 'media/image/configgroup'
    , 'media/image/manufacturer'
    , 'media/image/news'
    , 'media/image/newscategory'
    , 'media/image/opc'
    , 'media/image/product'
    , 'media/image/storage'
    , 'media/image/storage/categories'
    , 'media/image/storage/characteristics'
    , 'media/image/storage/characteristicvalues'
    , 'media/image/storage/configgroups'
    , 'media/image/storage/manufacturers'
    , 'media/image/storage/opc'
    , 'media/image/storage/variations'
    , 'media/image/storage/videothumbs'
    , 'media/image/variation'
    , 'media/video'
    , 'admin/templates_c'
    , 'admin/includes/emailpdfs'
    ];

    /**
     * Constructor of the file-check-object
     *
     * @param string $rootPath root-path of this shop-application
     */
    public function __construct($rootPath)
    {
        $this->rootPath    = $rootPath;
        $this->shopFolders = $this->collectWritableEntities();
    }

    /**
     * helper  to get the paths we want
     * (this should prevent functionality in a "difines"-config-file)
     *
     * @return array string-array of to-writable paths
     */
    private function collectWritableEntities(): array
    {
        return \array_map(function ($v) {
            if (\strpos($v, PFAD_ROOT) === 0) {
                $v = \substr($v, \strlen(PFAD_ROOT));
            }

            return \trim($v, '/\\');
        }, $this->writableEntities);
    }

    /**
     * helper to get all shop-folders as array, only for display-purposes
     *
     * @return array with folder-names (unchecked)
     */
    public function getFolders(): ?array
    {
        return $this->shopFolders;
    }

    /**
     * Check the folders (one times), given in "includes/defines.php"
     * and store the results in this object for later usage
     * (refactored and moved from "install_inc::gibBeschreibbareVerzeichnisseAssoc()")
     *
     * @return array  hash of shop-writable-folder, where value represents the state
     *                (1=writable, ''=not writable)
     */
    public function getFoldersChecked(): ?array
    {
        if ($this->foldersChecked === null) {
            if (empty($this->rootPath)) {
                return [];
            }

            $folders = [];
            $current = $this->shopFolders;
            if (\is_array($current) && count($current) > 0) {
                foreach ($current as $item) {
                    $folders[$item] = false;
                    $abs            = \PFAD_ROOT . $item;
                    if (\is_writable($abs)) {
                        // if entity (implicitly exists and) is writable
                        $folders[$item] = true;
                    } elseif (!\is_file($abs)) {
                        // if entity is not a file (implicitly not exists) try to write/create, and return the result
                        $writable       = (@\file_put_contents($abs, ' ') === 1);
                        $folders[$item] = $writable;
                        // cleanup if anything was written
                        if ($writable === true) {
                            \unlink($abs);
                        }
                    }
                }
            }
            $this->foldersChecked = $folders;
            return $folders;
        }

        return $this->foldersChecked;
    }


    /**
     * return a summary-result of this test, depending on the checked-folders-array.
     * if at least one folder is not writable, the test is failed
     *
     * @return bool
     */
    public function getIsPassed(): bool
    {
        $checkedFolders = $this->foldersChecked ?? $this->getFoldersChecked();
        foreach (\array_keys($checkedFolders) as $key) {
            (!(bool)$checkedFolders[$key])
                ? $this->passed = false
                : null;
        }

        return $this->passed;
    }


    /**
     * calculates a statistical number about the folders which need to be writable,
     * to show that in the "admin/permissioncheck"
     * (refactored and moved from permissioncheck_inc.php)
     *
     * @return stdClass contains the summery of folders/files and a value of 'invalids'
     */
    public function getFolderStats(): stdClass
    {
        $stats                = new stdClass();
        $stats->nCount        = 0;
        $stats->nCountInValid = 0;

        if (\is_array($this->foldersChecked) && count($this->foldersChecked) > 0) {
            foreach ($this->foldersChecked as $cDir => $isValid) {
                $stats->nCount++;
                if (!$isValid) {
                    $stats->nCountInValid++;
                }
            }
        }

        return $stats;
    }
}
