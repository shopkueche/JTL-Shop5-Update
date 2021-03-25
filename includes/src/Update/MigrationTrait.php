<?php

namespace JTL\Update;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;

/**
 * Trait MigrationTrait
 * @package JTL\Update
 */
trait MigrationTrait
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * executes query and returns misc data
     *
     * @param string $query - Statement to be executed
     * @param int    $return - what should be returned.
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function __execute(string $query, int $return)
    {
        if (\JTL_CHARSET === 'iso-8859-1') {
            $query = Text::utf8_convert_recursive($query, false);
        }

        return $this->getDB()->executeQuery($query, $return);
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @param string $query
     * @return int
     */
    public function execute(string $query)
    {
        return $this->__execute($query, ReturnType::AFFECTED_ROWS);
    }

    /**
     * @param string $query
     * @return \stdClass|bool
     */
    public function fetchOne(string $query)
    {
        return $this->__execute($query, ReturnType::SINGLE_OBJECT);
    }

    /**
     * @param string $query
     * @return array
     */
    public function fetchAll(string $query)
    {
        return $this->__execute($query, ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @param string $query
     * @return array
     */
    public function fetchArray(string $query)
    {
        return $this->__execute($query, ReturnType::ARRAY_OF_ASSOC_ARRAYS);
    }
}
