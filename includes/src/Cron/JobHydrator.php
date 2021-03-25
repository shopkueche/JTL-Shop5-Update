<?php

namespace JTL\Cron;

/**
 * Class JobHydrator
 * @package JTL\Cron
 */
final class JobHydrator
{
    /**
     * @var string[]
     */
    private static $mapping = [
        'cronID'        => 'CronID',
        'jobType'       => 'Type',
        'taskLimit'     => 'Limit',
        'tasksExecuted' => 'Executed',
        'foreignKeyID'  => 'ForeignKeyID',
        'foreignKey'    => 'ForeignKey',
        'tableName'     => 'TableName',
        'jobQueueID'    => 'QueueID',
        'lastStart'     => 'DateLastStarted',
        'startTime'     => 'StartTime',
        'frequency'     => 'Frequency',
        'isRunning'     => 'Running',
        'lastFinish'    => 'DateLastFinished'
    ];

    /**
     * @param string $key
     * @return string|null
     */
    private function getMapping(string $key): ?string
    {
        return self::$mapping[$key] ?? null;
    }

    /**
     * @param object $class
     * @param object $data
     * @return object
     */
    public function hydrate($class, $data)
    {
        foreach (\get_object_vars($data) as $key => $value) {
            if (($mapping = $this->getMapping($key)) !== null) {
                $method = 'set' . $mapping;
                $class->$method($value);
            }
        }

        return $class;
    }
}
