<?php declare(strict_types=1);

namespace JTL\Review;

use Exception;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use function Functional\map;

/**
 * Class ReviewAdminController
 * @package JTL\Review
 */
final class ReviewAdminController extends BaseController
{
    private $languageID;

    /**
     * ReviewAdminController constructor.
     * @param DbInterface                $db
     * @param JTLCacheInterface          $cache
     * @param AlertServiceInterface|null $alertService
     * @param JTLSmarty|null             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ?AlertServiceInterface $alertService = null,
        ?JTLSmarty $smarty = null
    ) {
        $this->db           = $db;
        $this->smarty       = $smarty;
        $this->config       = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->languageID   = (int)$_SESSION['editLanguageID'];
    }

    /**
     * @return string
     */
    public function handleRequest(): string
    {
        $step = 'bewertung_uebersicht';
        if (!Form::validateToken()) {
            return $step;
        }
        if (Request::verifyGPCDataInt('bewertung_editieren') === 1) {
            $step = 'bewertung_editieren';
            if ($this->edit($_POST)) {
                $step = 'bewertung_uebersicht';
                $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successRatingEdit'), 'successRatingEdit');
                if (Request::verifyGPCDataInt('nFZ') === 1) {
                    \header('Location: freischalten.php');
                    exit();
                }
            } else {
                $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
            }

            return $step;
        }
        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->setConfig($_POST);
        } elseif (Request::verifyGPCDataInt('bewertung_nicht_aktiv') === 1) {
            $this->handleInactive($_POST);
        } elseif (Request::verifyGPCDataInt('bewertung_aktiv') === 1) {
            $this->handleActive($_POST);
        }

        return $step;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function setConfig(array $data): bool
    {
        if (Request::verifyGPDataString('bewertung_guthaben_nutzen') === 'Y'
            && Request::verifyGPDataString('bewertung_freischalten') !== 'Y'
        ) {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorCreditUnlock'), 'errorCreditUnlock');
            return false;
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE]);
        $this->alertService->addAlert(
            Alert::TYPE_SUCCESS,
            \saveAdminSectionSettings(\CONF_BEWERTUNG, $data),
            'saveConf'
        );

        return true;
    }

    /**
     * handle request param 'bewertung_nicht_aktiv'
     *
     * @param array $data
     * @return bool
     */
    private function handleInactive(array $data): bool
    {
        if (isset($data['aktivieren']) && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->activate($data['kBewertung']) . __('successRatingUnlock'),
                'successRatingUnlock'
            );

            return true;
        }
        if (isset($data['loeschen']) && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->delete($_POST['kBewertung']) . __('successRatingDelete'),
                'successRatingDelete'
            );

            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function handleActive(array $data): bool
    {
        if (isset($data['loeschen']) && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->delete($data['kBewertung']) . __('successRatingDelete'),
                'successRatingDelete'
            );
        }
        if (isset($data['cArtNr'])) {
            $filtered = $this->db->queryPrepared(
                "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                    FROM tbewertung
                    LEFT JOIN tartikel 
                        ON tbewertung.kArtikel = tartikel.kArtikel
                    WHERE tbewertung.kSprache = :lang
                        AND (tartikel.cArtNr LIKE :cartnr OR tartikel.cName LIKE :cartnr)
                    ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC",
                ['lang' => $this->languageID, 'cartnr' => '%' . $data['cArtNr'] . '%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $this->smarty->assign('cArtNr', Text::filterXSS($data['cArtNr']))
                ->assign('filteredReviews', $filtered);
        }

        return true;
    }

    /**
     *
     */
    public function getOverview(): void
    {
        if (Request::verifyGPDataString('a') === 'delreply' && Form::validateToken()) {
            $this->removeReply(Request::verifyGPCDataInt('kBewertung'));
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                __('successRatingCommentDelete'),
                'successRatingCommentDelete'
            );
        }
        $activePagination   = $this->getActivePagination();
        $inactivePagination = $this->getInactivePagination();
        $sanitize           = static function ($e) {
            $e->kBewertung      = (int)$e->kBewertung;
            $e->kArtikel        = (int)$e->kArtikel;
            $e->kKunde          = (int)$e->kKunde;
            $e->kSprache        = (int)$e->kSprache;
            $e->nHilfreich      = (int)$e->nHilfreich;
            $e->nNichtHilfreich = (int)$e->nNichtHilfreich;
            $e->nSterne         = (int)$e->nSterne;
            $e->nAktiv          = (int)$e->nAktiv;
        };
        $inactiveReviews    = $this->db->query(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . $this->languageID . '
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC
            LIMIT ' . $inactivePagination->getLimitSQL(),
            ReturnType::COLLECTION
        )->each($sanitize)->toArray();
        $activeReviews      = $this->db->query(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . $this->languageID . '
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC
            LIMIT ' . $activePagination->getLimitSQL(),
            ReturnType::COLLECTION
        )->each($sanitize)->toArray();

        $this->smarty->assign('oPagiInaktiv', $inactivePagination)
            ->assign('oPagiAktiv', $activePagination)
            ->assign('inactiveReviews', $inactiveReviews)
            ->assign('activeReviews', $activeReviews)
            ->assign('oConfig_arr', \getAdminSectionSettings(\CONF_BEWERTUNG));
    }

    /**
     * @return Pagination
     */
    private function getInactivePagination(): Pagination
    {
        $totalCount = (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS nAnzahl
                FROM tbewertung
                WHERE kSprache = :lid
                    AND nAktiv = 0',
            ['lid' => $this->languageID],
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;

        return (new Pagination('inactive'))
            ->setItemCount($totalCount)
            ->assemble();
    }

    /**
     * @return Pagination
     */
    private function getActivePagination(): Pagination
    {
        $activeCount = (int)$this->db->queryPrepared(
            'SELECT COUNT(*) AS nAnzahl
                FROM tbewertung
                WHERE kSprache = :lid
                    AND nAktiv = 1',
            ['lid' => $this->languageID],
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;

        return (new Pagination('active'))
            ->setItemCount($activeCount)
            ->assemble();
    }

    /**
     * @param int $id
     * @return ReviewModel|null
     */
    public function getReview(int $id): ?ReviewModel
    {
        try {
            return ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function edit(array $data): bool
    {
        $id = Request::verifyGPCDataInt('kBewertung');
        try {
            $review = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return false;
        }
        if ($data['cAntwort'] !== $review->answer) {
            $review->setAnswerDate(!empty($data['cAntwort']) ? \date('Y-m-d') : null);
        }
        $review->setName($data['cName']);
        $review->setTitle($data['cTitel']);
        $review->setContent($data['cText']);
        $review->setStars((int)$data['nSterne']);
        $review->setAnswer(!empty($data['cAntwort']) ? $data['cAntwort'] : null);
        $review->save();
        $this->updateAverage($review->productID, $this->config['bewertung']['bewertung_freischalten']);

        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $review->productID]);

        return true;
    }

    /**
     * @param array $ids
     * @return int
     */
    private function delete(array $ids): int
    {
        $cacheTags = [];
        foreach (\array_map('\intval', $ids) as $id) {
            try {
                $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
            } catch (Exception $e) {
                continue;
            }
            $this->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $this->deleteReviewReward($model);
            $model->delete();
            $cacheTags[] = $model->getProductID();
        }
        $this->cache->flushTags(map($cacheTags, static function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return \count($cacheTags);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function activate(array $ids): int
    {
        $cacheTags = [];
        foreach (\array_map('\intval', $ids) as $i => $id) {
            try {
                $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
            } catch (Exception $e) {
                continue;
            }
            $model->setActive(1);
            $model->save(['active']);
            $this->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $this->addReward($model);
            $cacheTags[] = $model->getProductID();
        }
        $this->cache->flushTags(map($cacheTags, static function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return \count($cacheTags);
    }

    /**
     * @param int $id
     */
    private function removeReply(int $id): void
    {
        try {
            $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return;
        }
        $model->setAnswer(null);
        $model->setAnswerDate(null);
        $model->save(['answer', 'answerDate']);
    }

    /**
     * @param ReviewModel $review
     * @return int
     */
    private function deleteReviewReward(ReviewModel $review): int
    {
        $affected = 0;
        foreach ($review->getBonus() as $bonusItem) {
            /** @var ReviewBonusModel $bonusItem */
            $customer = $this->db->select('tkunde', 'kKunde', $bonusItem->getCustomerID());
            if ($customer !== null && $customer->kKunde > 0) {
                $balance = $customer->fGuthaben - $bonusItem->getBonus();
                $upd     = (object)['fGuthaben' => $balance > 0 ? $balance : 0];
                $this->db->update('tkunde', 'kKunde', $bonusItem->getCustomerID(), $upd);
                ++$affected;
            }
        }

        return $affected;
    }
}
