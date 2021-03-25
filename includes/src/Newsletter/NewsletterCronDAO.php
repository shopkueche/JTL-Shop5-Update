<?php declare(strict_types=1);

namespace JTL\Newsletter;

use DateTime;
use JTL\Shop;
use stdClass;

/**
 * Class NewsletterCronDAO
 * reflects all columns of the table `tcron`, except the auto_increment column
 * @package JTL\Newsletter
 */
class NewsletterCronDAO
{
    /**
     * @var int
     */
    private $foreignKeyID;

    /**
     * @var string
     */
    private $foreignKey;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $jobType;

    /**
     * @var int
     */
    private $frequency;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $startTime;

    /**
     * @var string
     */
    private $lastStart;

    /**
     * @var string
     */
    private $lastFinish;

    /**
     * NewsletterCronDAO constructor.
     * pre-define all table columns here, for inserting or updating them later
     * @throws \Exception
     */
    public function __construct()
    {
        $this->foreignKeyID = 0;
        $this->foreignKey   = 'kNewsletter';
        $this->tableName    = 'tnewsletter';
        $this->name         = 'Newsletter';
        $this->jobType      = 'newsletter';
        $this->startDate    = (new DateTime())->format('Y-m-d H:i:s');
        $this->startTime    = (new DateTime())->format('H:i:s');
        $this->lastStart    = '_DBNULL_';
        $this->lastFinish   = '_DBNULL_';
        $this->frequency    = Shop::getConfigValue(\CONF_NEWSLETTER, 'newsletter_send_delay');
    }

    /**
     * @return int
     */
    public function getForeignKeyID()
    {
        return $this->foreignKeyID;
    }

    /**
     * @param int $foreignKeyID
     * @return NewsletterCronDAO
     */
    public function setForeignKeyID($foreignKeyID): self
    {
        $this->foreignKeyID = $foreignKeyID;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     * @return NewsletterCronDAO
     */
    public function setForeignKey(string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return NewsletterCronDAO
     */
    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return NewsletterCronDAO
     */
    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string|null $startDate
     * @return NewsletterCronDAO
     */
    public function setStartDate($startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param string|null $startTime
     * @return NewsletterCronDAO
     */
    public function setStartTime($startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastStart()
    {
        return $this->lastStart;
    }

    /**
     * @param string|null $lastStart
     * @return NewsletterCronDAO
     */
    public function setLastStart($lastStart): self
    {
        $this->lastStart = $lastStart;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastFinish()
    {
        return $this->lastFinish;
    }

    /**
     * @param string|null $lastFinish
     * @return NewsletterCronDAO
     */
    public function setLastFinish($lastFinish): self
    {
        $this->lastFinish = $lastFinish;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getData(): stdClass
    {
        $res = new stdClass();
        foreach (\get_object_vars($this) as $k => $v) {
            $res->$k = $v;
        }

        return $res;
    }
}
