<?php declare(strict_types=1);

namespace JTL\dbeS;

use JTL\Helpers\FileSystem;
use Psr\Log\LoggerInterface;
use ZipArchive;

/**
 * Class FileHandler
 * @package JTL\dbeS
 */
class FileHandler
{
    private const TEMP_DIR = \PFAD_ROOT . \PFAD_DBES . \PFAD_SYNC_TMP;

    /**
     * @var string
     */
    private $unzipPath = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FileHandler constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __destruct()
    {
        if ($this->unzipPath !== '') {
            $this->removeTemporaryFiles($this->unzipPath, true);
        }
    }

    /**
     * @return string
     */
    public function getUnzipPath(): string
    {
        return $this->unzipPath;
    }

    /**
     * @param string $unzipPath
     */
    public function setUnzipPath(string $unzipPath): void
    {
        $this->unzipPath = $unzipPath;
    }

    /**
     * @param array|null $data
     * @return array|null
     */
    public function getSyncFiles(array $data = null): ?array
    {
        if (($zipFile = $this->checkFile($data)) === '') {
            return null;
        }
        $this->unzipPath = self::TEMP_DIR . \basename($zipFile) . '_' . \date('dhis') . '/';
        if (($syncFiles = $this->unzipSyncFiles($zipFile, $this->unzipPath)) === false) {
            $this->logger->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $this->unzipPath);
            $this->removeTemporaryFiles($zipFile);
            $syncFiles = null;
        }

        return $syncFiles;
    }

    /**
     * @param string $file
     * @param bool   $isDir
     * @return bool
     */
    private function removeTemporaryFiles(string $file, bool $isDir = false): bool
    {
        if (\KEEP_SYNC_FILES === true) {
            return false;
        }

        return $isDir ? FileSystem::delDirRecursively($file) : \unlink($file);
    }

    /**
     * @param string $zipFile
     * @param string $targetPath
     * @return array|bool
     */
    private function unzipSyncFiles(string $zipFile, string $targetPath)
    {
        $archive = new ZipArchive();
        if (($open = $archive->open($zipFile)) !== true) {
            $this->logger->error('unzipSyncFiles: Kann Datei ' . $zipFile . ' nicht öffnen. ErrorCode: ' . $open);

            return false;
        }
        $filenames = [];
        if (\is_dir($targetPath) || (\mkdir($targetPath) && \is_dir($targetPath))) {
            for ($i = 0; $i < $archive->numFiles; ++$i) {
                $filenames[] = $targetPath . $archive->getNameIndex($i);
            }
            if ($archive->numFiles > 0 && !$archive->extractTo($targetPath)) {
                return false;
            }
            $archive->close();

            return \array_filter(\array_map(static function ($e) {
                return \file_exists($e)
                    ? $e
                    : null;
            }, $filenames));
        }

        return false;
    }

    /**
     * @param int $code
     * @return string
     */
    private function getErrorMessage(int $code): string
    {
        switch ($code) {
            case 0:
                $error = 'Datei kam an, aber Dateigröße 0 [0]';
                break;
            case 1:
                $error = 'Dateigröße > upload_max_filesize directive in php.ini [1]';
                break;
            case 2:
                $error = 'Dateigröße > MAX_FILE_SIZE [2]';
                break;
            case 3:
                $error = 'Datei wurde nur zum Teil hochgeladen [3]';
                break;
            case 4:
                $error = 'Es wurde keine Datei hochgeladen [4]';
                break;
            case 6:
                $error = 'Es fehlt ein TMP-Verzeichnis für HTTP Datei-Uploads! Bitte an Hoster wenden! [6]';
                break;
            case 7:
                $error = 'Datei konnte nicht auf Datenträger gespeichert werden! [7]';
                break;
            case 8:
                $error = 'Dateiendung nicht akzeptiert, bitte an Hoster werden! [8]';
                break;
            default:
                $error = 'Fehler beim Datenaustausch - Datei kam nicht an oder Größe 0!';
                break;
        }

        return $error;
    }

    /**
     * @param array|null $data
     * @return string
     */
    public function checkFile($data = null): string
    {
        $files = $data ?? $_FILES;

        if (!isset($files['data'])) {
            return '';
        }
        if (!empty($files['data']['error']) || (isset($files['data']['size']) && $files['data']['size'] === 0)) {
            $this->logger->error(
                'ERROR: incoming: ' . $files['data']['name'] . ' size:' . $files['data']['size'] .
                ' err:' . $files['data']['error']
            );
            $error = $this->getErrorMessage($files['data']['error']);
            \syncException($error . "\n" . \print_r($files, true), \FREIDEFINIERBARER_FEHLER);
        }
        $target = self::TEMP_DIR . \basename($files['data']['tmp_name']);
        \move_uploaded_file($files['data']['tmp_name'], $target);
        $files['data']['tmp_name'] = $target;

        return $target;
    }
}
