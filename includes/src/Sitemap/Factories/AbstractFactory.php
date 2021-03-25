<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use JTL\DB\DbInterface;

/**
 * Class AbstractFactory
 * @package JTL\Sitemap\Factories
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseURL;

    /**
     * @var string
     */
    protected $baseImageURL;

    /**
     * AbstractFactory constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param string      $baseURL
     * @param string      $baseImageURL
     */
    public function __construct(DbInterface $db, array $config, string $baseURL, string $baseImageURL)
    {
        $this->db           = $db;
        $this->config       = $config;
        $this->baseURL      = $baseURL;
        $this->baseImageURL = $baseImageURL;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res           = \get_object_vars($this);
        $res['db']     = '*truncated*';
        $res['config'] = '*truncated*';

        return $res;
    }
}
