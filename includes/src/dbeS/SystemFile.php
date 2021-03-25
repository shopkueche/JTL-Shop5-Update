<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SystemFile
 * @package JTL\dbeS
 */
class SystemFile
{
    /**
     * @var int
     */
    public $kFileID;

    /**
     * @var string
     */
    public $cFilepath;

    /**
     * @var string
     */
    public $cRelFilepath;

    /**
     * @var string
     */
    public $cFilename;

    /**
     * @var string
     */
    public $cDirname;

    /**
     * @var string
     */
    public $cExtension;

    /**
     * @var int
     */
    public $nUploaded;

    /**
     * @var int
     */
    public $nBytes;

    /**
     * @param int    $kFileID
     * @param string $cFilepath
     * @param string $cRelFilepath
     * @param string $cFilename
     * @param string $cDirname
     * @param string $cExtension
     * @param int    $nUploaded
     * @param int    $nBytes
     */
    public function __construct(
        $kFileID,
        $cFilepath,
        $cRelFilepath,
        $cFilename,
        $cDirname,
        $cExtension,
        $nUploaded,
        $nBytes
    ) {
        $this->kFileID      = $kFileID;
        $this->cFilepath    = $cFilepath;
        $this->cRelFilepath = $cRelFilepath;
        $this->cFilename    = $cFilename;
        $this->cDirname     = $cDirname;
        $this->cExtension   = $cExtension;
        $this->nUploaded    = $nUploaded;
        $this->nBytes       = $nBytes;
    }
}
