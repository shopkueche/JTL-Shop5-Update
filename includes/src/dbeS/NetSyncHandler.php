<?php

namespace JTL\dbeS;

use Exception;
use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class NetSyncHandler
 * @package JTL\dbeS
 */
class NetSyncHandler
{
    /**
     * @var NetSyncHandler
     */
    private static $instance;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NetSyncHandler constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @throws Exception
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        if (self::$instance !== null) {
            throw new Exception('Class ' . __CLASS__ . ' already created');
        }
        self::$instance = $this;
        $this->db       = $db;
        $this->logger   = $logger;
        if (!$this->isAuthenticated()) {
            static::throwResponse(NetSyncResponse::ERRORLOGIN);
        }
        $this->request((int)$_REQUEST['e']);
    }

    /**
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        // by token
        if (isset($_REQUEST['t'])) {
            \session_id($_REQUEST['t']);
            \session_start();

            return $_SESSION['bAuthed'];
        }
        // by syncdata
        $name          = \urldecode($_REQUEST['uid']);
        $pass          = \urldecode($_REQUEST['upwd']);
        $authenticated = (\strlen($name) > 0 && \strlen($pass) > 0)
            ? (new Synclogin($this->db, $this->logger))->checkLogin($name, $pass)
            : false;
        if ($authenticated) {
            \session_start();
            $_SESSION['bAuthed'] = $authenticated;
        }

        return $authenticated;
    }

    /**
     * @param int        $code
     * @param null|mixed $data
     */
    protected static function throwResponse(int $code, $data = null): void
    {
        $response         = new stdClass();
        $response->nCode  = $code;
        $response->cToken = '';
        $response->oData  = null;
        if ($code === 0) {
            $response->cToken = \session_id();
            $response->oData  = $data;
        }
        echo \json_encode($response);
        exit;
    }

    /**
     * @param int $request
     */
    protected function request($request): void
    {
    }

    /**
     * @param string          $class
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public static function create(string $class, DbInterface $db, LoggerInterface $logger): void
    {
        if (self::$instance === null && \class_exists($class)) {
            new $class($db, $logger);
            \set_exception_handler([$class, 'exception']);
        }
    }

    /**
     * @param string $filename
     * @param string $mimetype
     * @param string $outname
     */
    public function streamFile($filename, $mimetype, $outname = ''): void
    {
        $browser = $this->getBrowser($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = 'application/octet-stream';
            if (($browser === 'ie') || ($browser === 'opera')) {
                $mimetype = 'application/octetstream';
            }
        }

        @\ob_end_clean();
        @\ini_set('zlib.output_compression', 'Off');

        \header('Pragma: public');
        \header('Content-Transfer-Encoding: none');

        if ($outname === '') {
            $outname = \basename($filename);
        }
        if ($browser === 'ie') {
            \header('Content-Type: ' . $mimetype);
            \header('Content-Disposition: inline; filename="' . $outname . '"');
        } else {
            \header('Content-Type: ' . $mimetype . '; name="' . $outname . '"');
            \header('Content-Disposition: attachment; filename=' . $outname);
        }
        $size = @\filesize($filename);
        if ($size) {
            \header('Content-length: ' . $size);
        }
        \readfile($filename);
        \unlink($filename);
        exit;
    }

    /**
     * @param string $userAgent
     * @return string
     */
    private function getBrowser(string $userAgent): string
    {
        $browser = 'other';
        if (\preg_match('/^Opera(\/| )([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'opera';
        } elseif (\preg_match('/^MSIE ([0-9].[0-9]{1,2})/', $userAgent) === 1) {
            $browser = 'ie';
        }

        return $browser;
    }

    /**
     * @param string $baseDir
     * @return array
     */
    protected function getFolderStruct(string $baseDir): array
    {
        $folders = [];
        $baseDir = \realpath($baseDir);
        foreach (\scandir($baseDir, \SCANDIR_SORT_ASCENDING) as $folder) {
            if ($folder === '.' || $folder === '..' || $folder[0] === '.') {
                continue;
            }
            $pathName = $baseDir . \DIRECTORY_SEPARATOR . $folder;
            if (\is_dir($pathName)) {
                $systemFolder              = new SystemFolder($folder, $pathName);
                $systemFolder->oSubFolders = $this->getFolderStruct($pathName);
                $folders[]                 = $systemFolder;
            }
        }

        return $folders;
    }

    /**
     * @param string $baseDir
     * @return array
     */
    protected function getFilesStruct(string $baseDir): array
    {
        $index   = 0;
        $files   = [];
        $baseDir = \realpath($baseDir);
        foreach (\scandir($baseDir, \SCANDIR_SORT_ASCENDING) as $file) {
            if ($file === '.' || $file === '..' || $file[0] === '.') {
                continue;
            }
            $pathName = $baseDir . \DIRECTORY_SEPARATOR . $file;
            if (\is_file($pathName)) {
                $pathinfo = \pathinfo($pathName);
                $files[]  = new SystemFile(
                    $index++,
                    $pathName,
                    \basename($pathName),
                    $pathinfo['filename'],
                    $pathinfo['dirname'],
                    $pathinfo['extension'],
                    \filemtime($pathName),
                    \filesize($pathName)
                );
            }
        }

        return $files;
    }
}
