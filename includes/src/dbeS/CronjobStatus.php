<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class CronjobStatus
 * @package JTL\dbeS
 */
class CronjobStatus
{
    /**
     * @var int
     */
    public $kCron;

    /**
     * @var string
     */
    public $cExportformat;

    /**
     * @var string
     */
    public $cStartDate;

    /**
     * @var int
     */
    public $nRepeat;

    /**
     * @var int
     */
    public $nDone;

    /**
     * @var int
     */
    public $nOverall;

    /**
     * @var string
     */
    public $cLastStartDate;

    /**
     * @var string
     */
    public $cNextStartDate;

    /**
     * @param int    $kCron
     * @param string $cExportformat
     * @param string $cStartDate
     * @param int    $nRepeat
     * @param int    $nDone
     * @param int    $nOverall
     * @param string $cLastStartDate
     * @param string $cNextStartDate
     */
    public function __construct(
        $kCron,
        $cExportformat,
        $cStartDate,
        $nRepeat,
        $nDone,
        $nOverall,
        $cLastStartDate,
        $cNextStartDate
    ) {
        $this->kCron          = $kCron;
        $this->cExportformat  = $cExportformat;
        $this->cStartDate     = $cStartDate;
        $this->nRepeat        = $nRepeat;
        $this->nDone          = $nDone;
        $this->nOverall       = $nOverall;
        $this->cLastStartDate = $cLastStartDate;
        $this->cNextStartDate = $cNextStartDate;
    }
}
