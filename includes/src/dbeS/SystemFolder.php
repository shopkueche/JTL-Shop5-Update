<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SystemFolder
 * @package JTL\dbeS
 */
class SystemFolder
{
    /**
     * @var string
     */
    public $cBaseName;

    /**
     * @var string
     */
    public $cBasePath;

    /**
     * @var array
     */
    public $oSubFolders;

    /**
     * @param string $cBaseName
     * @param string $cBasePath
     * @param array  $oSubFolders
     */
    public function __construct($cBaseName = '', $cBasePath = '', $oSubFolders = [])
    {
        $this->cBaseName   = $cBaseName;
        $this->cBasePath   = $cBasePath;
        $this->oSubFolders = $oSubFolders;
    }
}
