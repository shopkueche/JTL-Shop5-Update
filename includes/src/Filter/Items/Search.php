<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\States\BaseSearchQuery;
use JTL\Filter\StateSQL;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class Search
 * @package JTL\Filter\Items
 */
class Search extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     * @former kSuchCache
     */
    private $searchCacheID = 0;

    /**
     * @var string
     */
    private $error;

    /**
     * @var int
     * @former kSuchanfrage
     */
    private $searchID;

    /**
     * @var bool
     */
    public $bExtendedJTLSearch = false;

    /**
     * @var array
     */
    public static $mapping = [
        'kSuchanfrage' => 'Value',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * Search constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setVisibility($this->getConfig('navigationsfilter')['suchtrefferfilter_nutzen'])
             ->setFrontendName(Shop::isAdmin() ? __('filterSearch') : Shop::Lang()->get('searchFilter'))
             ->setUrlParam('sf');
    }

    /**
     * @return int
     */
    public function getSearchCacheID(): int
    {
        return $this->searchCacheID;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setSearchCacheID(int $id): FilterInterface
    {
        $this->searchCacheID = $id;

        return $this;
    }

    /**
     * @param string $errorMsg
     * @return $this
     */
    public function setError($errorMsg): FilterInterface
    {
        $this->error = $errorMsg;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->searchID = $value;

        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getValue()
    {
        return $this->searchID;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $seo = $this->productFilter->getDB()->executeQueryPrepared(
            'SELECT cSuche
                FROM tsuchanfrage
                WHERE kSuchanfrage = :kkey
                  AND kSprache = :languageID',
            [
                'kkey'       => $this->getValue(),
                'languageID' => (int)$_SESSION['kSprache']
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (!empty($seo->cSuche)) {
            $this->setName($seo->cSuche);
        }

        return $this;
    }

    /**
     * @param string $searchTerm
     * @param int    $languageID
     * @return $this
     */
    public function setQueryID(string $searchTerm, int $languageID): FilterInterface
    {
        $searchQuery = null;
        if ($languageID > 0 && \mb_strlen($searchTerm) > 0) {
            $searchQuery = $this->productFilter->getDB()->select(
                'tsuchanfrage',
                'cSuche',
                $searchTerm,
                'kSprache',
                $languageID
            );
        }
        $this->setValue((isset($searchQuery->kSuchanfrage) && $searchQuery->kSuchanfrage > 0)
            ? (int)$searchQuery->kSuchanfrage
            : 0);

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kSuchanfrage';
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tsuchanfrage';
    }

    /**
     * @param int    $hits
     * @param string $query
     * @param bool   $real
     * @param int    $languageID
     * @param bool   $filterSpam
     * @return bool
     * @former suchanfragenSpeichern
     */
    public function saveQuery(
        int $hits,
        string $query = '',
        bool $real = false,
        int $languageID = 0,
        bool $filterSpam = true
    ): bool {
        if ($query === '') {
            $query = $this->getName();
        }
        if (empty($query) || $this->productFilter->getFilterCount() > 0) {
            // only save non-filtered queries
            return false;
        }
        $query       = \str_replace(["'", '\\', '*', '%'], '', $query);
        $languageID  = $languageID > 0 ? $languageID : $this->getLanguageID();
        $tempQueries = \explode(';', $query);
        $blacklist   = $this->productFilter->getDB()->select(
            'tsuchanfrageblacklist',
            'kSprache',
            $languageID,
            'cSuche',
            $this->productFilter->getDB()->escape($tempQueries[0])
        );
        if ($filterSpam && $blacklist !== null && !empty($blacklist->kSuchanfrageBlacklist)) {
            return false;
        }
        // Ist md5(IP) bereits X mal im Cache
        $maxHits       = (int)$this->getConfig('artikeluebersicht')['livesuche_max_ip_count'];
        $userCacheHits = (int)$this->productFilter->getDB()->executeQueryPrepared(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfragencache
                WHERE kSprache = :lang
                AND cIP = :ip',
            ['lang' => $languageID, 'ip' => Request::getRealIP()],
            ReturnType::SINGLE_OBJECT
        )->cnt;
        $ipUsed        = $this->productFilter->getDB()->select(
            'tsuchanfragencache',
            'kSprache',
            $languageID,
            'cSuche',
            $query,
            'cIP',
            Request::getRealIP(),
            false,
            'kSuchanfrageCache'
        );
        if (!$filterSpam || ($userCacheHits < $maxHits && ($ipUsed === null || empty($ipUsed->kSuchanfrageCache)))) {
            $searchQueryCache           = new stdClass();
            $searchQueryCache->kSprache = $languageID;
            $searchQueryCache->cIP      = Request::getRealIP();
            $searchQueryCache->cSuche   = $query;
            $searchQueryCache->dZeit    = 'NOW()';
            $this->productFilter->getDB()->insert('tsuchanfragencache', $searchQueryCache);
            // Cacheeinträge die > 1 Stunde sind, löschen
            $this->productFilter->getDB()->query(
                'DELETE
                    FROM tsuchanfragencache
                    WHERE dZeit < DATE_SUB(NOW(), INTERVAL 1 HOUR)',
                ReturnType::AFFECTED_ROWS
            );
            if ($hits > 0) {
                $searchQuery                  = new stdClass();
                $searchQuery->kSprache        = $languageID;
                $searchQuery->cSuche          = $query;
                $searchQuery->nAnzahlTreffer  = $hits;
                $searchQuery->nAnzahlGesuche  = 1;
                $searchQuery->dZuletztGesucht = 'NOW()';
                $searchQuery->cSeo            = Seo::getSeo($query);
                $searchQuery->cSeo            = Seo::checkSeo($searchQuery->cSeo);
                $previuousQuery               = $this->productFilter->getDB()->select(
                    'tsuchanfrage',
                    'kSprache',
                    (int)$searchQuery->kSprache,
                    'cSuche',
                    $query,
                    null,
                    null,
                    false,
                    'kSuchanfrage'
                );
                if ($real && $previuousQuery !== null && $previuousQuery->kSuchanfrage > 0) {
                    $this->productFilter->getDB()->query(
                        'UPDATE tsuchanfrage
                            SET nAnzahlTreffer = ' . (int)$searchQuery->nAnzahlTreffer . ',
                                nAnzahlGesuche = nAnzahlGesuche + 1,
                                dZuletztGesucht = NOW()
                            WHERE kSuchanfrage = ' . (int)$previuousQuery->kSuchanfrage,
                        ReturnType::AFFECTED_ROWS
                    );
                } elseif (!isset($previuousQuery->kSuchanfrage) || !$previuousQuery->kSuchanfrage) {
                    $this->productFilter->getDB()->delete(
                        'tsuchanfrageerfolglos',
                        ['kSprache', 'cSuche'],
                        [(int)$searchQuery->kSprache, $query]
                    );

                    return $this->productFilter->getDB()->insert('tsuchanfrage', $searchQuery) > 0;
                }
            } else {
                $queryMiss                  = new stdClass();
                $queryMiss->kSprache        = $languageID;
                $queryMiss->cSuche          = $query;
                $queryMiss->nAnzahlGesuche  = 1;
                $queryMiss->dZuletztGesucht = 'NOW()';
                $oldMiss                    = $this->productFilter->getDB()->select(
                    'tsuchanfrageerfolglos',
                    'kSprache',
                    (int)$queryMiss->kSprache,
                    'cSuche',
                    $query,
                    null,
                    null,
                    false,
                    'kSuchanfrageErfolglos'
                );
                if ($real && $oldMiss !== null && $oldMiss->kSuchanfrageErfolglos > 0) {
                    $this->productFilter->getDB()->query(
                        'UPDATE tsuchanfrageerfolglos
                            SET nAnzahlGesuche = nAnzahlGesuche + 1,
                                dZuletztGesucht = NOW()
                            WHERE kSuchanfrageErfolglos = ' .
                        (int)$oldMiss->kSuchanfrageErfolglos,
                        ReturnType::AFFECTED_ROWS
                    );
                } else {
                    $this->productFilter->getDB()->delete(
                        'tsuchanfrage',
                        ['kSprache', 'cSuche'],
                        [(int)$queryMiss->kSprache, $query]
                    );
                    $this->productFilter->getDB()->insert('tsuchanfrageerfolglos', $queryMiss);
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $count        = 0;
        $searchCache  = [];
        $searchFilter = $this->productFilter->getSearchFilter();
        if (\is_array($searchFilter)) {
            $count       = \count($searchFilter);
            $searchCache = \array_map(static function ($f) {
                /** @var Search $f */
                return $f->getValue();
            }, $searchFilter);
        } elseif ($searchFilter->getSearchCacheID() > 0) {
            $searchCache[] = $searchFilter->getSearchCacheID();
            $count         = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $searchCache = [$value];
            $count       = 1;
        }

        return (new Join())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache,
                            MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              JOIN tsuchcache
                                  ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                              JOIN tsuchanfrage
                                  ON tsuchanfrage.cSuche = tsuchcache.cSuche
                                  AND tsuchanfrage.kSuchanfrage IN (' . \implode(',', $searchCache) . ')
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                        ) AS jfSuche')
            ->setOn('jfSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from ' . __METHOD__);
    }

    /**
     * generate search cache entries for activated search queries
     *
     * @param int $limit
     * @return $this
     */
    private function generateSearchCaches(int $limit = 0): self
    {
        $allQueries = $this->productFilter->getDB()->query(
            'SELECT tsuchanfrage.cSuche FROM tsuchanfrage
                LEFT JOIN tsuchcache
                    ON tsuchcache.cSuche = tsuchanfrage.cSuche
                WHERE tsuchanfrage.nAktiv = 1
                    AND tsuchcache.kSuchCache IS NULL
                ORDER BY tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche'
                . ($limit > 0 ? ' LIMIT ' . $limit : ''),
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($allQueries as $nonCachedQuery) {
            $bsq = new BaseSearchQuery($this->productFilter);
            $bsq->init($nonCachedQuery->cSuche)
                ->setName($nonCachedQuery->cSuche);
            $bsq->editSearchCache();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $limit   = (int)$this->getConfig('navigationsfilter')['suchtrefferfilter_anzahl'];
        $options = [];
        if ($this->getConfig('navigationsfilter')['suchtrefferfilter_nutzen'] === 'N') {
            return $options;
        }
        $sql = (new StateSQL())->from($this->productFilter->getCurrentStateData());
        $sql->setSelect([
            'tsuchanfrage.kSuchanfrage',
            'tsuchcache.kSuchCache',
            'tsuchanfrage.cSuche',
            'tartikel.kArtikel'
        ]);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);
        $sql->addJoin((new Join())
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcachetreffer')
            ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('JOIN2 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchcache')
            ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache')
            ->setOrigin(__CLASS__));
        $sql->addJoin((new Join())
            ->setComment('JOIN3 from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('tsuchanfrage')
            ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche
                        AND tsuchanfrage.kSprache = ' . $this->getLanguageID())
            ->setOrigin(__CLASS__));
        $sql->addCondition('tsuchanfrage.nAktiv = 1');

        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = $this->getCacheID($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $nLimit = $limit > 0 ? ' LIMIT ' . $limit : '';
        $this->generateSearchCaches($limit > 0 ? $limit : 10);
        $searchFilters = $this->productFilter->getDB()->query(
            'SELECT ssMerkmal.kSuchanfrage, ssMerkmal.kSuchCache, ssMerkmal.cSuche, COUNT(*) AS nAnzahl
                FROM (' . $baseQuery . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $nLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        $searchQueries = [];
        if ($this->productFilter->hasSearch()) {
            $searchQueries[] = $this->productFilter->getSearch()->getValue();
        }
        if ($this->productFilter->hasSearchFilter()) {
            foreach ($this->productFilter->getSearchFilter() as $item) {
                if ($item->getValue() > 0) {
                    $searchQueries[] = (int)$item->getValue();
                }
            }
        }
        // entferne bereits gesetzte Filter aus dem Ergebnis-Array
        foreach ($searchFilters as $j => $searchFilter) {
            foreach ($searchQueries as $searchQuery) {
                if ($searchFilter->kSuchanfrage === $searchQuery) {
                    unset($searchFilters[$j]);
                    break;
                }
            }
        }
        if (\is_array($searchFilters)) {
            $searchFilters = \array_merge($searchFilters);
        }
        $additionalFilter = new self($this->productFilter);
        $count            = \count($searchFilters);
        $stepPrio         = $count > 0
            ? ($searchFilters[0]->nAnzahl - $searchFilters[$count - 1]->nAnzahl) / 9
            : 0;
        $activeValues     = \array_map(
            static function ($f) {
                // @todo: create method for this logic
                /** @var Search $f */
                return $f->getValue();
            },
            $this->productFilter->getSearchFilter()
        );
        foreach ($searchFilters as $searchFilter) {
            $class = \random_int(1, 10);
            if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $stepPrio > 0) {
                $class = \round(($searchFilter->nAnzahl - $searchFilters[$count - 1]->nAnzahl) / $stepPrio) + 1;
            }
            $options[] = (new Option())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                ))
                ->setData('cSuche', $searchFilter->cSuche)
                ->setData('kSuchanfrage', $searchFilter->kSuchanfrage)
                ->setIsActive(\in_array((int)$searchFilter->kSuchanfrage, $activeValues, true))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setClass((string)$class)
                ->setParam($this->getUrlParam())
                ->setName($searchFilter->cSuche)
                ->setValue((int)$searchFilter->kSuchanfrage)
                ->setCount((int)$searchFilter->nAnzahl);
        }
        $this->options = $options;
        $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);

        return $options;
    }
}
