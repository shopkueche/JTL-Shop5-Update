<?php declare(strict_types=1);

namespace JTL\Filter\States;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\filter;

/**
 * Class BaseSearchQuery
 * @package JTL\Filter\States
 */
class BaseSearchQuery extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kSuchanfrage' => 'ID',
        'kSuchcache'   => 'SearchCacheID',
        'cSuche'       => 'Name',
        'Fehler'       => 'Error'
    ];

    /**
     * @former kSuchanfrage
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     * @former kSuchCache
     */
    private $searchCacheID = 0;

    /**
     * @var string
     */
    public $error;

    /**
     * BaseSearchQuery constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
            ->setUrlParam('suche')
            ->setUrlParamSEO(null);
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
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setName($name): FilterInterface
    {
        $this->error = null;
        $minChars    = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (\mb_strlen($name) > 0 || Request::getVar('qs') === '') {
            \preg_match(
                '/[\S]{' . $minChars . ',}/u',
                \str_replace(' ', '', $name),
                $hits
            );
            if (\count($hits) === 0) {
                $this->error = Shop::Lang()->get('expressionHasTo') . ' ' .
                    $minChars . ' ' .
                    Shop::Lang()->get('lettersDigits');
            }
        }

        return parent::setName($name);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return ($this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery())
            ? \urlencode($this->productFilter->getRealSearch()->cSuche)
            : $this->value;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id): FilterInterface
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParam(): string
    {
        return $this->productFilter->getRealSearch() !== null && !$this->productFilter->hasSearchQuery()
            ? 'suche'
            : parent::getUrlParam();
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
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $seo = $this->productFilter->getDB()->executeQueryPrepared(
            "SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key",
            ['key' => $this->getID()],
            ReturnType::SINGLE_OBJECT
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($seo->kSprache) && $language->kSprache === (int)$seo->kSprache) {
                $this->cSeo[$language->kSprache] = $seo->cSeo;
            }
        }
        if (!empty($seo->cSuche)) {
            $this->setName($seo->cSuche);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kSuchanfrage';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tsuchanfrage';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $searchCacheIDs = [];
        $searchFilter   = $this->productFilter->getBaseState();
        if (\is_array($searchFilter)) {
            $count = \count($searchFilter);
            foreach ($searchFilter as $item) {
                if ($item->getSearchCacheID() > 0) {
                    $searchCacheIDs[] = $item->getSearchCacheID();
                }
            }
        } elseif ($searchFilter->getSearchCacheID() > 0) {
            $searchCacheIDs[] = $searchFilter->getSearchCacheID();
            $count            = 1;
        } else {
            $searchCacheIDs = [$searchFilter->getValue()];
            $count          = 1;
        }

        return (new Join())
            ->setType('JOIN')
            ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                          MIN(tsuchcachetreffer.nSort) AS nSort
                              FROM tsuchcachetreffer
                              WHERE tsuchcachetreffer.kSuchCache IN (' . \implode(',', $searchCacheIDs) . ') 
                              #JOIN tsuchcache
                              #    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                              #JOIN tsuchanfrage
                              #    ON tsuchanfrage.cSuche = tsuchcache.cSuche
                              #    AND tsuchanfrage.kSuchanfrage IN (' . \implode(',', $searchCacheIDs) . ') 
                              GROUP BY tsuchcachetreffer.kArtikel
                              HAVING COUNT(*) = ' . $count . '
                          ) AS jSuche')
            ->setOn('jSuche.kArtikel = tartikel.kArtikel')
            ->setComment('JOIN1 from ' . __METHOD__)
            ->setOrigin(__CLASS__);
    }

    /**
     * @inheritDoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options  = [];
        $naviConf = $this->getConfig('navigationsfilter');
        if ($naviConf['suchtrefferfilter_nutzen'] === 'N') {
            return $options;
        }
        $max   = (int)($naviConf['suchtrefferfilter_anzahl'] ?? 0);
        $limit = $max > 0 ? (' LIMIT ' . $max) : '';

        $sql = (new StateSQL())->from($this->productFilter->getCurrentStateData());
        $sql->setSelect(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel']);
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
        $searchFilters  = $this->productFilter->getDB()->query(
            'SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, COUNT(*) AS nAnzahl
                FROM (' . $baseQuery . ') AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche' . $limit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        $searchQueryIDs = [];
        if ($this->productFilter->hasSearch()) {
            $searchQueryIDs[] = (int)$this->productFilter->getSearch()->getValue();
        }
        if ($this->productFilter->hasSearchFilter()) {
            foreach ($this->productFilter->getSearchFilter() as $searchFilter) {
                if ($searchFilter->getValue() > 0) {
                    $searchQueryIDs[] = (int)$searchFilter->getValue();
                }
            }
        }
        // entferne bereits gesetzte Filter aus dem Ergebnis-Array
        foreach ($searchFilters as $j => $searchFilter) {
            foreach ($searchQueryIDs as $searchQuery) {
                if ($searchFilter->kSuchanfrage === $searchQuery) {
                    unset($searchFilters[$j]);
                    break;
                }
            }
        }
        if (\is_array($searchFilters)) {
            $searchFilters = \array_merge($searchFilters);
        }
        //baue URL
        $additionalFilter = new self($this->productFilter);
        // Priorität berechnen
        $nPrioStep = 0;
        $nCount    = \count($searchFilters);
        if ($nCount > 0) {
            $nPrioStep = ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9;
        }
        foreach ($searchFilters as $searchFilter) {
            $fo = (new Option())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                ))
                ->setClass((string)\rand(1, 10))
                ->setParam($this->getUrlParam())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($searchFilter->cSuche)
                ->setValue((int)$searchFilter->kSuchanfrage)
                ->setCount((int)$searchFilter->nAnzahl);
            if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep > 0) {
                $fo->setClass(
                    (string)(\round(
                        ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                        $nPrioStep
                    ) + 1)
                );
            }
            $options[] = $fo;
        }
        $this->options = $options;
        $this->productFilter->getCache()->set($cacheID, $options, [\CACHING_GROUP_FILTER]);

        return $options;
    }

    /**
     * @param string $query
     * @param int    $langIDExt
     * @return string
     * @former mappingBeachten
     */
    private function getQueryMapping(string $query, int $langIDExt = 0): string
    {
        $langID = $langIDExt > 0
            ? $langIDExt
            : $this->getLanguageID();
        if (\mb_strlen($query) > 0) {
            $querymappingTMP = $this->productFilter->getDB()->select(
                'tsuchanfragemapping',
                'kSprache',
                $langID,
                'cSuche',
                $query
            );
            $querymapping    = $querymappingTMP;
            while (!empty($querymappingTMP->cSucheNeu)) {
                $querymappingTMP = $this->productFilter->getDB()->select(
                    'tsuchanfragemapping',
                    'kSprache',
                    $langID,
                    'cSuche',
                    $querymappingTMP->cSucheNeu
                );
                if (!empty($querymappingTMP->cSucheNeu)) {
                    $querymapping = $querymappingTMP;
                }
            }
            if (!empty($querymapping->cSucheNeu)) {
                $query = $querymapping->cSucheNeu;
            }
        }

        return $query ?? '';
    }

    /**
     * @param int $langIDExt
     * @return int
     */
    public function editSearchCache($langIDExt = 0): int
    {
        // Mapping beachten
        $query = $this->getQueryMapping($this->getName() ?? '', $langIDExt);
        $this->setName($query);
        $langID = $langIDExt > 0
            ? (int)$langIDExt
            : $this->getLanguageID();
        // Suchcache wurde zwar gefunden, ist jedoch nicht mehr gültig
        $this->productFilter->getDB()->query(
            'DELETE tsuchcache, tsuchcachetreffer
                FROM tsuchcache
                LEFT JOIN tsuchcachetreffer 
                    ON tsuchcachetreffer.kSuchCache = tsuchcache.kSuchCache
                WHERE tsuchcache.dGueltigBis IS NOT NULL
                    AND DATE_ADD(tsuchcache.dGueltigBis, INTERVAL 5 MINUTE) < NOW()',
            ReturnType::AFFECTED_ROWS
        );
        // Suchcache checken, ob bereits vorhanden
        $searchCache = $this->productFilter->getDB()->executeQueryPrepared(
            'SELECT kSuchCache
                FROM tsuchcache
                WHERE kSprache = :lang
                    AND cSuche = :search
                    AND (dGueltigBis > NOW() OR dGueltigBis IS NULL)',
            [
                'lang'   => $langID,
                'search' => $query
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($searchCache->kSuchCache) && $searchCache->kSuchCache > 0) {
            return (int)$searchCache->kSuchCache; // Gib gültigen Suchcache zurück
        }
        // wenn kein Suchcache vorhanden
        $minChars = ($min = (int)$this->getConfig('artikeluebersicht')['suche_min_zeichen']) > 0
            ? $min
            : 3;
        if (\mb_strlen($query) < $minChars) {
            require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';
            $this->error = \lang_suche_mindestanzahl($query, $minChars);

            return 0;
        }
        // Suchausdruck aufbereiten
        $search = $this->prepareSearchQuery($query);
        $tmp    = $search;
        if (\count($search) === 0) {
            return 0;
        }
        // Array mit nach Prio sort. Suchspalten holen
        $rows                   = self::getSearchRows($this->getConfig());
        $cols                   = $this->getSearchColumnClasses($rows);
        $searchCache            = new stdClass();
        $searchCache->kSprache  = $langID;
        $searchCache->cSuche    = $query;
        $searchCache->dErstellt = 'NOW()';
        $kSuchCache             = $this->productFilter->getDB()->insert('tsuchcache', $searchCache);

        if ($this->getConfig('artikeluebersicht')['suche_fulltext'] !== 'N' && $this->isFulltextIndexActive()) {
            $searchCache->kSuchCache = $kSuchCache;

            return $this->editFullTextSearchCache(
                $searchCache,
                $rows,
                $search,
                $this->getConfig('artikeluebersicht')['suche_max_treffer'],
                $this->getConfig('artikeluebersicht')['suche_fulltext']
            );
        }

        if ($kSuchCache <= 0) {
            return 0;
        }

        if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
            $sql = 'SELECT ' . $kSuchCache . ', IF(tartikel.kVaterArtikel > 0, 
                        tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, ';
        } else {
            $sql = 'SELECT ' . $kSuchCache . ', IF(kVaterArtikel > 0, 
                        kVaterArtikel, kArtikel) AS kArtikelTMP, ';
        }
        // Shop2 Suche - mehr als 3 Suchwörter *
        if (\count($search) > 3) {
            $sql .= ' 1 ';
            if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                $sql .= ' FROM tartikel
                                LEFT JOIN tartikelsprache
                                    ON tartikelsprache.kArtikel = tartikel.kArtikel
                                    AND tartikelsprache.kSprache = ' . $this->getLanguageID();
            } else {
                $sql .= ' FROM tartikel ';
            }
            $sql .= ' WHERE ';

            foreach ($rows as $i => $col) {
                if ($i > 0) {
                    $sql .= ' OR';
                }
                $sql .= '(';
                foreach ($tmp as $j => $cSuch) {
                    if ($j > 0) {
                        $sql .= ' AND';
                    }
                    $sql .= ' ' . $col . " LIKE '%" . $cSuch . "%'";
                }
                $sql .= ')';
            }
        } else {
            $brackets = 0;
            $prio     = 1;
            foreach ($rows as $i => $col) {
                // Fülle bei 1, 2 oder 3 Suchwörtern aufsplitten
                switch (\count($tmp)) {
                    case 1: // Fall 1, nur ein Suchwort
                        // "A"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " = '" . $tmp[0] . "', " . ++$prio . ', ';
                        }
                        // "A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . "', " . ++$prio . ', ';
                        }
                        // "%_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . "%', " . ++$prio . ', ';
                        }
                        // "%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "A%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[0] . "%', " . ++$prio . ', ';
                        }
                        // "%A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . "', " . ++$prio . ', ';
                        }
                        // "%A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . "%', " . ++$prio . ', ';
                        }
                        break;
                    case 2: // Fall 2, zwei Suchwörter
                        // "A_B"
                        $nonAllowed = [2];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[0] . ' ' . $tmp[1] . "', " . ++$prio . ', ';
                        }
                        // "B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[1] . ' ' . $tmp[0] . "', " . ++$prio . ', ';
                        }
                        // "A_B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[0] . ' ' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "B_A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '" . $tmp[1] . ' ' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A_B"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . ' ' . $tmp[1] . "', " . ++$prio . ', ';
                        }
                        // "%_B_A"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . ' ' . $tmp[0] . "', " . ++$prio . ', ';
                        }
                        // "%_A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . ' ' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "%_B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . ' ' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%A_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . ' ' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "%B_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[1] . ' ' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . ' ' . $tmp[1] . "%', " . ++$prio . ', ';
                        }
                        // "%_B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . ' ' . $tmp[0] . "%', " . ++$prio . ', ';
                        }
                        // "%A_B%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . ' ' . $tmp[1] . "%', " . ++$prio . ', ';
                        }
                        // "%B_A%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[1] . ' ' . $tmp[0] . "%', " . ++$prio . ', ';
                        }
                        // "%_A%_B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . '% ' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "%_B%_A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . '% ' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A_%B_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . ' %' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "%_B_%A_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . ' %' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        // "%_A%_%B_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[0] . '% %' . $tmp[1] . " %', " . ++$prio . ', ';
                        }
                        // "%_B%_%A_%"
                        $nonAllowed = [2, 3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '% " . $tmp[1] . '% %' . $tmp[0] . " %', " . ++$prio . ', ';
                        }
                        break;
                    case 3: // Fall 3, drei Suchwörter
                        // "%A_%_B_%_C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . ' % ' .
                                $tmp[1] . ' % ' . $tmp[2] . "%', " . ++$prio . ', ';
                        }
                        // "%_A_% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '% " . $tmp[0] . " %') AND (" . $col .
                                " LIKE '% " . $tmp[1] . " %') AND (" . $col .
                                " LIKE '% " . $tmp[2] . " %'), " . ++$prio . ', ';
                        }
                        // "%_A_% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '" . $tmp[0] . "') AND (" . $col .
                                " LIKE '" . $tmp[1] . "') AND (" . $col .
                                " LIKE '%" . $tmp[2] . "%'), " . ++$prio . ', ';
                        }
                        // "%_A_% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '% " . $tmp[0] . " %') AND (" . $col .
                                " LIKE '%" . $tmp[1] . "%') AND (" . $col .
                                " LIKE '% " . $tmp[2] . " %'), " . ++$prio . ', ';
                        }
                        // "%_A_% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '% " . $tmp[0] . " %') AND (" . $col .
                                " LIKE '%" . $tmp[1] . "%') AND (" . $col .
                                " LIKE '%" . $tmp[2] . "%'), " . ++$prio . ', ';
                        }
                        // "%A% AND %_B_% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '%" . $tmp[0] . "%') AND (" . $col .
                                " LIKE '% " . $tmp[1] . " %') AND (" . $col .
                                " LIKE '% " . $tmp[2] . " %'), " . ++$prio . ', ';
                        }
                        // "%A% AND %_B_% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '%" . $tmp[0] . "%') AND (" . $col .
                                " LIKE '% " . $tmp[1] . " %') AND (" . $col .
                                " LIKE '%" . $tmp[2] . "%'), " . ++$prio . ', ';
                        }
                        // "%A% AND %B% AND %_C_%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '%" . $tmp[0] . "%') AND (" . $col .
                                " LIKE '%" . $tmp[1] . "%') AND (" . $col .
                                " LIKE '% " . $tmp[2] . " %'), " . ++$prio . ', ';
                        }
                        // "%A%B%C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF(' . $col . " LIKE '%" . $tmp[0] . '%' .
                                $tmp[1] . '%' . $tmp[2] . "%', " . ++$prio . ', ';
                        }
                        // "%A% AND %B% AND %C%"
                        $nonAllowed = [3];
                        if ($this->checkColumnClasses($cols, $col, $nonAllowed)) {
                            ++$brackets;
                            $sql .= 'IF((' . $col . " LIKE '%" . $tmp[0] . "%') AND (" . $col .
                                " LIKE '%" . $tmp[1] . "%') AND (" . $col .
                                " LIKE '%" . $tmp[2] . "%'), " . ++$prio . ', ';
                        }
                        break;
                }

                if ($i === (\count($rows) - 1)) {
                    $sql .= '254)';
                }
            }

            for ($i = 0; $i < ($brackets - 1); ++$i) {
                $sql .= ')';
            }

            if ($this->getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                $sql .= ' FROM tartikel
                            LEFT JOIN tartikelsprache
                                ON tartikelsprache.kArtikel = tartikel.kArtikel
                                AND tartikelsprache.kSprache = ' . $this->getLanguageID();
            } else {
                $sql .= ' FROM tartikel ';
            }
            $sql .= ' WHERE ';

            foreach ($rows as $i => $col) {
                if ($i > 0) {
                    $sql .= ' OR';
                }
                $sql .= '(';

                foreach ($tmp as $j => $cSuch) {
                    if ($j > 0) {
                        $sql .= ' AND';
                    }
                    $sql .= ' ' . $col . " LIKE '%" . $cSuch . "%'";
                }
                $sql .= ')';
            }
        }
        $this->productFilter->getDB()->query(
            'INSERT INTO tsuchcachetreffer ' .
            $sql .
            ' GROUP BY kArtikelTMP
                LIMIT ' . (int)$this->getConfig('artikeluebersicht')['suche_max_treffer'],
            ReturnType::AFFECTED_ROWS
        );

        return $kSuchCache;
    }

    /**
     * @param string $query
     * @return array
     */
    public function prepareSearchQuery($query): array
    {
        $query          = \str_replace(["'", '\\', '*', '%'], '', \strip_tags($query));
        $searchArray    = [];
        $parts          = \explode(' ', $query);
        $query_stripped = \stripslashes($query);
        if ($query_stripped[0] !== '"' || $query_stripped[\mb_strlen($query_stripped) - 1] !== '"') {
            foreach ($parts as $i => $searchString) {
                if (\mb_strpos($searchString, '+') !== false) {
                    $searchPart = \explode('+', $searchString);
                    foreach ($searchPart as $part) {
                        $part = \trim($part);
                        if ($part) {
                            $searchArray[] = $part;
                        }
                    }
                } else {
                    $searchString = \trim($searchString);
                    if ($searchString) {
                        $searchArray[] = $searchString;
                    }
                }
            }
        } else {
            $searchArray[] = \str_replace('"', '', $query_stripped);
        }

        return $searchArray;
    }

    /**
     * @param stdClass $searchCache
     * @param array    $searchCols
     * @param array    $searchQueries
     * @param int      $limit
     * @param string   $fullText
     * @return int
     * @former bearbeiteSuchCacheFulltext
     */
    private function editFullTextSearchCache(
        $searchCache,
        $searchCols,
        $searchQueries,
        int $limit = 0,
        $fullText = 'Y'
    ): int {
        if ($searchCache->kSuchCache > 0) {
            $productCols = \array_map(static function ($item) {
                $items = \explode('.', $item, 2);

                return 'tartikel.' . $items[1];
            }, $searchCols);

            $langCols = \array_filter($searchCols, static function ($item) {
                return \preg_match('/tartikelsprache\.(.*)/', $item) ? true : false;
            });

            $score = 'MATCH (' . \implode(', ', $productCols) . ")
                        AGAINST ('" . \implode(' ', $searchQueries) . "' IN NATURAL LANGUAGE MODE) ";
            if ($fullText === 'B') {
                $match = 'MATCH (' . \implode(', ', $productCols) . ")
                        AGAINST ('" . \implode('* ', $searchQueries) . "*' IN BOOLEAN MODE) ";
            } else {
                $match = $score;
            }

            $sql = 'SELECT ' . $searchCache->kSuchCache . ' AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, '
                    . $score . ' AS score
                    FROM tartikel
                    WHERE ' . $match . $this->productFilter->getFilterSQL()->getStockFilterSQL() . ' ';

            if (Shop::getLanguageID() > 0 && !LanguageHelper::isDefaultLanguageActive()) {
                $score = 'MATCH (' . \implode(', ', $langCols) . ")
                            AGAINST ('" . \implode(' ', $searchQueries) . "' IN NATURAL LANGUAGE MODE)";
                if ($fullText === 'B') {
                    $score = 'MATCH (' . \implode(', ', $langCols) . ")
                            AGAINST ('" . \implode('* ', $searchQueries) . "*' IN BOOLEAN MODE)";
                } else {
                    $match = $score;
                }
                $sql .= 'UNION DISTINCT
                SELECT ' . $searchCache->kSuchCache . ' AS kSuchCache,
                    IF(tartikel.kVaterArtikel > 0, tartikel.kVaterArtikel, tartikel.kArtikel) AS kArtikelTMP, '
                    . $score . ' AS score
                    FROM tartikel
                    INNER JOIN tartikelsprache ON tartikelsprache.kArtikel = tartikel.kArtikel
                    WHERE ' . $match . $this->productFilter->getFilterSQL()->getStockFilterSQL() . ' ';
            }

            $this->productFilter->getDB()->query(
                'INSERT INTO tsuchcachetreffer
                        SELECT kSuchCache, kArtikelTMP, ROUND(MAX(score) * -10)
                        FROM ( ' . $sql . ' ) AS i
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikelsichtbarkeit.kArtikel = i.kArtikelTMP
                            AND tartikelsichtbarkeit.kKundengruppe = ' . Frontend::getCustomerGroup()->getID() . '
                        WHERE tartikelsichtbarkeit.kKundengruppe IS NULL
                        GROUP BY kSuchCache, kArtikelTMP' . ($limit > 0 ? ' LIMIT ' . $limit : ''),
                ReturnType::AFFECTED_ROWS
            );
        }

        return $searchCache->kSuchCache;
    }

    /**
     * @param array $searchCols
     * @return array
     */
    public function getSearchColumnClasses($searchCols): array
    {
        $result = [];
        if (\is_array($searchCols) && \count($searchCols) > 0) {
            foreach ($searchCols as $columns) {
                // Klasse 1: Artikelname und Artikel SEO
                if (\mb_strpos($columns, 'cName') !== false
                    || \mb_strpos($columns, 'cSeo') !== false
                    || \mb_strpos($columns, 'cSuchbegriffe') !== false
                ) {
                    $result[1][] = $columns;
                }
                // Klasse 2: Artikelname und Artikel SEO
                if (\mb_strpos($columns, 'cKurzBeschreibung') !== false
                    || \mb_strpos($columns, 'cBeschreibung') !== false
                    || \mb_strpos($columns, 'cAnmerkung') !== false
                ) {
                    $result[2][] = $columns;
                }
                // Klasse 3: Artikelname und Artikel SEO
                if (\mb_strpos($columns, 'cArtNr') !== false
                    || \mb_strpos($columns, 'cBarcode') !== false
                    || \mb_strpos($columns, 'cISBN') !== false
                    || \mb_strpos($columns, 'cHAN') !== false
                ) {
                    $result[3][] = $columns;
                }
            }
        }

        return $result;
    }

    /**
     * @param array  $searchCols
     * @param string $searchCol
     * @param array  $nonAllowed
     * @return bool
     */
    public function checkColumnClasses($searchCols, $searchCol, $nonAllowed): bool
    {
        if (\is_array($searchCols)
            && \is_array($nonAllowed)
            && \count($searchCols) > 0
            && \mb_strlen($searchCol) > 0
            && \count($nonAllowed) > 0
        ) {
            foreach ($nonAllowed as $class) {
                if (isset($searchCols[$class]) && \count($searchCols[$class]) > 0) {
                    foreach ($searchCols[$class] as $searchColumnnKlasse) {
                        if ($searchColumnnKlasse === $searchCol) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isFulltextIndexActive(): bool
    {
        static $active = null;

        if ($active === null) {
            $active = $this->productFilter->getDB()->query(
                "SHOW INDEX FROM tartikel 
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                ReturnType::SINGLE_OBJECT
            )
                && $this->productFilter->getDB()->query(
                    "SHOW INDEX 
                        FROM tartikelsprache 
                        WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    ReturnType::SINGLE_OBJECT
                );
        }

        return $active;
    }

    /**
     * @param array|null $config
     * @return array
     * @former gibSuchSpalten()
     */
    public static function getSearchRows(array $config = null): array
    {
        $searchRows = [];
        $config     = $config ?? Shop::getSettings([\CONF_ARTIKELUEBERSICHT]);
        for ($i = 0; $i < 10; ++$i) {
            $searchRows[] = self::getPrioritizedRows($searchRows, $config);
        }

        return filter($searchRows, static function ($r) {
            return $r !== '';
        });
    }

    /**
     * @param array      $exclude
     * @param array|null $conf
     * @return string
     * @former gibMaxPrioSpalte()
     */
    public static function getPrioritizedRows(array $exclude, array $conf = null): string
    {
        $max     = 0;
        $current = '';
        $prefix  = 'tartikel.';
        $conf    = $conf['artikeluebersicht'] ?? Shop::getSettings([\CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];
        if (!LanguageHelper::isDefaultLanguageActive()) {
            $prefix = 'tartikelsprache.';
        }
        if ($conf['suche_prio_name'] > $max && !\in_array($prefix . 'cName', $exclude, true)) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cName';
        }
        if ($conf['suche_prio_name'] > $max && !\in_array($prefix . 'cSeo', $exclude, true)) {
            $max     = $conf['suche_prio_name'];
            $current = $prefix . 'cSeo';
        }
        if ($conf['suche_prio_suchbegriffe'] > $max && !\in_array('tartikel.cSuchbegriffe', $exclude, true)) {
            $max     = $conf['suche_prio_suchbegriffe'];
            $current = 'tartikel.cSuchbegriffe';
        }
        if ($conf['suche_prio_artikelnummer'] > $max && !\in_array('tartikel.cArtNr', $exclude, true)) {
            $max     = $conf['suche_prio_artikelnummer'];
            $current = 'tartikel.cArtNr';
        }
        if ($conf['suche_prio_kurzbeschreibung'] > $max && !\in_array($prefix . 'cKurzBeschreibung', $exclude, true)) {
            $max     = $conf['suche_prio_kurzbeschreibung'];
            $current = $prefix . 'cKurzBeschreibung';
        }
        if ($conf['suche_prio_beschreibung'] > $max && !\in_array($prefix . 'cBeschreibung', $exclude, true)) {
            $max     = $conf['suche_prio_beschreibung'];
            $current = $prefix . 'cBeschreibung';
        }
        if ($conf['suche_prio_ean'] > $max && !\in_array('tartikel.cBarcode', $exclude, true)) {
            $max     = $conf['suche_prio_ean'];
            $current = 'tartikel.cBarcode';
        }
        if ($conf['suche_prio_isbn'] > $max && !\in_array('tartikel.cISBN', $exclude, true)) {
            $max     = $conf['suche_prio_isbn'];
            $current = 'tartikel.cISBN';
        }
        if ($conf['suche_prio_han'] > $max && !\in_array('tartikel.cHAN', $exclude, true)) {
            $max     = $conf['suche_prio_han'];
            $current = 'tartikel.cHAN';
        }
        if ($conf['suche_prio_anmerkung'] > $max && !\in_array('tartikel.cAnmerkung', $exclude, true)) {
            $current = 'tartikel.cAnmerkung';
        }

        return $current;
    }
}
