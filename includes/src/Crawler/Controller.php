<?php declare(strict_types=1);

namespace JTL\Crawler;

use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Services\JTL\AlertServiceInterface;
use stdClass;

/**
 * Class Controller
 * @package JTL\Crawler
 */
class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var AlertServiceInterface
     */
    protected $alertService;

    /**
     * Crawler constructor.
     * @param DbInterface                $db
     * @param JTLCacheInterface          $cache
     * @param AlertServiceInterface|null $alertService
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, AlertServiceInterface $alertService = null)
    {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
    }

    /**
     * @param int $id
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getCrawler(int $id): array
    {
        $crawler = $this->db->queryPrepared(
            'SELECT * FROM tbesucherbot WHERE kBesucherBot = :id ',
            ['id' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($crawler) === 0) {
            throw new \InvalidArgumentException('Provided crawler id ' . $id . ' not found.');
        }

        return $crawler;
    }

    /**
     * @return array
     */
    public function getAllCrawlers(): array
    {
        $cacheID = 'crawler';
        if (($crawlers = $this->cache->get($cacheID)) === false) {
            $crawlers = $this->db->query(
                'SELECT * FROM tbesucherbot ORDER BY kBesucherBot DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $this->cache->set($cacheID, $crawlers, [\CACHING_GROUP_CORE]);
        }

        return $crawlers;
    }

    /**
     * @param string $userAgent
     * @return object|bool
     */
    public function getByUserAgent(string $userAgent)
    {
        if ($userAgent === '') {
            return false;
        }
        $crawlers = $this->getAllCrawlers();
        $result   = \array_filter($crawlers, static function ($item) use ($userAgent) {
            return \mb_stripos($item->cUserAgent, $userAgent) !== false;
        });
        $result   = \array_values($result);

        return \count($result) > 0 ? (object)$result[0] : false;
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function deleteCrawler(array $ids): bool
    {
        $where_in = '(' . \implode(',', \array_map('\intval', $ids)) . ')';
        $this->db->executeQuery(
            'DELETE FROM tbesucherbot 
                WHERE kBesucherBot IN ' . $where_in . ' ',
            ReturnType::DEFAULT
        );
        $this->cache->flush('crawler');

        return true;
    }

    /**
     * @param object $item
     * @return int
     */
    public function saveCrawler(object $item): int
    {
        $this->cache->flush('crawler');
        if (isset($item->kBesucherBot, $item->cBeschreibung) && !empty($item->kBesucherBot)) {
            return $this->db->update(
                'tbesucherbot',
                'kBesucherBot',
                $item->kBesucherBot,
                $item
            );
        }

        return $this->db->insert(
            'tbesucherbot',
            $item
        );
    }

    /**
     * @return object|mixed
     */
    public function checkRequest()
    {
        $crawler = false;
        if (Form::validateToken() === false
            && (Request::postInt('save_crawler') || Request::postInt('delete_crawler'))
        ) {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorCSRF'), 'errorCSRF');

            return $crawler;
        }
        if (Request::postInt('delete_crawler') === 1) {
            $selectedCrawler = Request::postVar('selectedCrawler');
            $this->deleteCrawler($selectedCrawler);
        }
        if (Request::postInt('save_crawler') === 1) {
            if (!empty(Request::postVar('useragent')) && !empty(Request::postVar('description'))) {
                $item                = new stdClass();
                $item->kBesucherBot  = (int)Request::postInt('id');
                $item->cUserAgent    = Request::postVar('useragent');
                $item->cBeschreibung = Request::postVar('description');
                $result              = $this->saveCrawler($item);
                if ($result === -1) {
                    $this->alertService->addAlert(
                        Alert::TYPE_ERROR,
                        __('missingCrawlerFields'),
                        'missingCrawlerFields'
                    );
                } else {
                    \header('Location: statistik.php?s=3&tab=settings');
                }
            } else {
                $this->alertService->addAlert(Alert::TYPE_ERROR, __('missingCrawlerFields'), 'missingCrawlerFields');
            }
        }
        if (Request::verifyGPCDataInt('edit') === 1 || Request::verifyGPCDataInt('new') === 1) {
            $crawlerId = Request::verifyGPCDataInt('id');
            $crawler   = new Crawler();
            if ($crawlerId > 0) {
                $item = $this->getCrawler($crawlerId);
                $crawler->map($item);
            }
        }

        return $crawler;
    }
}
