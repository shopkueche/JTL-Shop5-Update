<?php

namespace JTL\Services\JTL;

use JTL\Boxes\FactoryInterface;
use JTL\Boxes\Renderer\RendererInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Filter\ProductFilter;
use JTL\Smarty\JTLSmarty;

/**
 * Interface BoxServiceInterface
 * @package JTL\Services\JTL
 */
interface BoxServiceInterface
{
    /**
     * @param array             $config
     * @param FactoryInterface  $factory
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param JTLSmarty         $smarty
     * @param RendererInterface $renderer
     * @return BoxServiceInterface
     */
    public static function getInstance(
        array $config,
        FactoryInterface $factory,
        DbInterface $db,
        JTLCacheInterface $cache,
        JTLSmarty $smarty,
        RendererInterface $renderer
    ): BoxServiceInterface;

    /**
     * BoxServiceInterface constructor.
     * @param array             $config
     * @param FactoryInterface  $factory
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param JTLSmarty         $smarty
     * @param RendererInterface $renderer
     */
    public function __construct(
        array $config,
        FactoryInterface $factory,
        DbInterface $db,
        JTLCacheInterface $cache,
        JTLSmarty $smarty,
        RendererInterface $renderer
    );

    /**
     * @param int      $productID
     * @param int|null $limit
     */
    public function addRecentlyViewed(int $productID, int $limit = null): void;

    /**
     * @param int  $pageType
     * @param bool $global
     * @return array|bool
     */
    public function getVisibility(int $pageType, bool $global = true);

    /**
     * @param int          $boxID
     * @param int          $pageType
     * @param string|array $filter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $pageType, $filter = ''): int;

    /**
     * @param ProductFilter $pf
     * @return bool
     */
    public function showBoxes(ProductFilter $pf): bool;

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     */
    public function getRawData(): array;

    /**
     * @return array
     */
    public function getBoxes(): array;

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     */
    public function compatGet(): array;

    /**
     * @param array $positionedBoxes
     * @param int   $pageType
     * @return array
     * @throws \Exception
     * @throws \SmartyException
     */
    public function render(array $positionedBoxes, int $pageType): array;

    /**
     * @param int  $pageType
     * @param bool $activeOnly
     * @return array
     */
    public function buildList(int $pageType = \PAGE_UNBEKANNT, bool $activeOnly = true): array;
}
