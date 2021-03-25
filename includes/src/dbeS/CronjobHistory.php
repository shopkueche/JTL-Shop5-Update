<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class CronjobHistory
 * @package JTL\dbeS
 */
class CronjobHistory
{
    /**
     * @var string
     */
    public $cExportformat;

    /**
     * @var string
     */
    public $cDateiname;

    /**
     * @var int
     */
    public $nDone;

    /**
     * @var string
     */
    public $cLastStartDate;

    /**
     * @param string $cExportformat
     * @param string $cDateiname
     * @param int    $nDone
     * @param string $cLastStartDate
     */
    public function __construct($cExportformat, $cDateiname, $nDone, $cLastStartDate)
    {
        $this->cExportformat  = $cExportformat;
        $this->cDateiname     = $cDateiname;
        $this->nDone          = $nDone;
        $this->cLastStartDate = $cLastStartDate;
    }
}
