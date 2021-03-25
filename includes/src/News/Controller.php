<?php declare(strict_types=1);

namespace JTL\News;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\CMS;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\every;
use function Functional\map;
use function Functional\pluck;

/**
 * Class Controller
 * @package JTL\News
 */
class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * @var string
     */
    private $noticeMsg = '';

    /**
     * @var int
     */
    private $currentNewsType;

    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param JTLSmarty   $smarty
     */
    public function __construct(DbInterface $db, array $config, JTLSmarty $smarty)
    {
        $this->config = $config;
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @param array $params
     * @return int
     */
    public function getPageType(array $params): int
    {
        if (!isset($_SESSION['NewsNaviFilter'])) {
            $_SESSION['NewsNaviFilter'] = new stdClass();
        }
        if (Request::verifyGPCDataInt('nSort') > 0) {
            $_SESSION['NewsNaviFilter']->nSort = Request::verifyGPCDataInt('nSort');
        } elseif (Request::verifyGPCDataInt('nSort') === -1) {
            $_SESSION['NewsNaviFilter']->nSort = -1;
        } elseif (!isset($_SESSION['NewsNaviFilter']->nSort)) {
            $_SESSION['NewsNaviFilter']->nSort = 1;
        }
        if ((int)$params['cDatum'] === -1) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        } elseif (\mb_strlen($params['cDatum']) > 0) {
            $_SESSION['NewsNaviFilter']->cDatum = \mb_substr_count($params['cDatum'], '-') > 0
                ? Text::filterXSS($params['cDatum'])
                : -1;
        } elseif (!isset($_SESSION['NewsNaviFilter']->cDatum)) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
        if ($params['nNewsKat'] > 0) {
            $_SESSION['NewsNaviFilter']->nNewsKat = $params['nNewsKat'];
        } elseif (!isset($_SESSION['NewsNaviFilter']->nNewsKat) || $params['nNewsKat'] === -1) {
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        }
        if ($this->config['news']['news_benutzen'] !== 'Y') {
            return ViewType::NEWS_DISABLED;
        }
        $this->currentNewsType = ViewType::NEWS_OVERVIEW;
        if ($params['kNews'] > 0) {
            $this->currentNewsType = ViewType::NEWS_DETAIL;
        } elseif ($params['kNewsKategorie'] > 0) {
            $this->currentNewsType = ViewType::NEWS_CATEGORY;
        } elseif ($params['kNewsMonatsUebersicht'] > 0) {
            $this->currentNewsType = ViewType::NEWS_MONTH_OVERVIEW;
            if (($data = $this->getMonthOverview($params['kNewsMonatsUebersicht'])) !== null) {
                $_SESSION['NewsNaviFilter']->cDatum   = (int)$data->nMonat . '-' . (int)$data->nJahr;
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            }
        }
        $this->smarty->assign('oDatum_arr', $this->getNewsDates(self::getFilterSQL(true)))
                     ->assign('nPlausiValue_arr', [
                         'cKommentar' => 0,
                         'nAnzahl'    => 0,
                         'cEmail'     => 0,
                         'cName'      => 0,
                         'captcha'    => 0
                     ]);

        return $this->currentNewsType;
    }

    /**
     * @param int $id
     * @return stdClass|null
     */
    private function getMonthOverview(int $id): ?stdClass
    {
        return $this->db->queryPrepared(
            "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
                FROM tnewsmonatsuebersicht
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kNewsMonatsUebersicht'
                    AND tseo.kKey = :nmi
                    AND tseo.kSprache = :lid
                WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :nmi",
            [
                'nmi' => $id,
                'lid' => Shop::getLanguageID()
            ],
            ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @param Item       $newsItem
     * @param Pagination $pagination
     */
    public function displayItem(Item $newsItem, Pagination $pagination): void
    {
        $newsCategories = $this->getNewsCategories($newsItem->getID());
        foreach ($newsCategories as $category) {
            $category->cURL     = URL::buildURL($category, \URLART_NEWSKATEGORIE);
            $category->cURLFull = URL::buildURL($category, \URLART_NEWSKATEGORIE, true);
        }
        $comments            = $newsItem->getComments()->getThreadedItems()->filter(static function ($item) {
            return $item->isActive();
        });
        $itemsPerPageOptions = ($perPage = (int)$this->config['news']['news_kommentare_anzahlproseite']) > 0
            ? [$perPage, $perPage * 2, $perPage * 5]
            : [10, 20, 50];
        $pagination->setItemsPerPageOptions($itemsPerPageOptions)
                   ->setItemCount($comments->count())
                   ->assemble();
        if ($pagination->getItemsPerPage() > 0) {
            $comments = $comments->forPage(
                $pagination->getPage() + 1,
                $pagination->getItemsPerPage()
            );
        }
        if (!$newsItem->isVisible()) {
            $this->smarty->assign('cNewsErr', true)
                ->assign('newsItem', $newsItem);
        } else {
            $conf = Shop::getConfig([\CONF_NEWS]);
            $this->smarty->assign('oNewsKommentar_arr', $comments)
                ->assign('comments', $comments)
                ->assign('cNewsErr', false)
                ->assign('oPagiComments', $pagination)
                ->assign('oNewsKategorie_arr', $newsCategories)
                ->assign('oNewsArchiv', $newsItem)
                ->assign('newsItem', $newsItem)
                ->assign('userCanComment', Frontend::getCustomer()->getID() > 0)
                ->assign('oNews_arr', $conf['news']['news_benutzen'] === 'Y'
                    ? CMS::getHomeNews($conf)
                    : []);
        }
    }

    /**
     * @param Pagination $pagination
     * @param int        $categoryID
     * @param int        $monthOverviewID
     * @param int        $customerGroupID
     * @return Category
     */
    public function displayOverview(
        Pagination $pagination,
        int $categoryID = 0,
        int $monthOverviewID = 0,
        $customerGroupID = 0
    ): Category {
        $category = new Category($this->db);
        if ($categoryID > 0) {
            $category->load($categoryID);
        } elseif ($monthOverviewID > 0) {
            $category->getMonthOverview($monthOverviewID);
        } else {
            $category->getOverview(self::getFilterSQL());
        }
        $items         = $category->filterAndSortItems($customerGroupID, Shop::getLanguageID());
        $newsCountShow = ($conf = (int)$this->config['news']['news_anzahl_uebersicht']) > 0
            ? $conf
            : 10;
        $pagination->setItemsPerPageOptions([$newsCountShow, $newsCountShow * 2, $newsCountShow * 5])
                   ->setItemCount($category->getItems()->count())
                   ->assemble();
        if ($pagination->getItemsPerPage() > -1) {
            $items = $items->forPage(
                $pagination->getPage() + 1,
                $pagination->getItemsPerPage()
            );
        }
        $this->smarty->assign('oNewsUebersicht_arr', $items)
                     ->assign('newsItems', $items)
                     ->assign('noarchiv', 0)
                     ->assign('oNewsKategorie_arr', $this->getAllNewsCategories(true))
                     ->assign('nSort', $_SESSION['NewsNaviFilter']->nSort)
                     ->assign('cDatum', $_SESSION['NewsNaviFilter']->cDatum)
                     ->assign('oNewsCat', $category)
                     ->assign('oPagination', $pagination)
                     ->assign('kNewsKategorie', $_SESSION['NewsNaviFilter']->nNewsKat);
        if ($items->count() === 0) {
            $this->smarty->assign('noarchiv', 1);
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            $_SESSION['NewsNaviFilter']->cDatum   = -1;
        }

        \executeHook(\HOOK_NEWS_PAGE_NEWSUEBERSICHT, [
            'category' => $category,
            'items'    => $items
        ]);

        return $category;
    }

    /**
     * @param bool $activeOnly
     * @return Collection
     */
    public function getAllNewsCategories(bool $activeOnly = false): Collection
    {
        $itemList = new CategoryList($this->db);
        $ids      = map($this->db->query(
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node INNER JOIN tnewskategorie AS parent
                WHERE node.lvl > 0 
                    AND parent.lvl > 0 ' . ($activeOnly ? ' AND node.nAktiv = 1 ' : '') .
            ' GROUP BY node.kNewsKategorie
                ORDER BY node.lft, node.nSort ASC',
            ReturnType::ARRAY_OF_OBJECTS
        ), static function ($e) {
            return (int)$e->id;
        });
        $itemList->createItems($ids);

        return $itemList->generateTree();
    }

    /**
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function addComment(int $id, array $data): bool
    {
        if ($this->config['news']['news_kommentare_nutzen'] !== 'Y') {
            return false;
        }
        $checks    = self::checkComment($data, $id, $this->config);
        $checkedOK = every($checks, static function ($e) {
            return $e === 0;
        });

        \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_PLAUSI);

        if (Frontend::getCustomer()->getID() > 0) {
            if ($checkedOK) {
                $comment             = new stdClass();
                $comment->kNews      = (int)$data['kNews'];
                $comment->kKunde     = (int)$_SESSION['Kunde']->kKunde;
                $comment->nAktiv     = $this->config['news']['news_kommentare_freischalten'] === 'Y'
                    ? 0
                    : 1;
                $comment->cName      = $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname[0] . '.';
                $comment->cEmail     = $_SESSION['Kunde']->cMail;
                $comment->cKommentar = Text::htmlentities(Text::filterXSS($data['cKommentar']));
                $comment->dErstellt  = 'now()';

                \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$comment]);

                $this->db->insert('tnewskommentar', $comment);

                if ($this->config['news']['news_kommentare_freischalten'] === 'Y') {
                    $this->noticeMsg .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br>';
                } else {
                    $this->noticeMsg .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br>';
                }
            } else {
                $this->errorMsg .= self::getCommentErrors($checks);
                $this->smarty->assign('nPlausiValue_arr', $checks)
                             ->assign('cPostVar_arr', Text::filterXSS($data));
            }
        }

        return true;
    }

    /**
     * @param array $post
     * @param int   $newsID
     * @param array $config
     * @return array
     */
    public static function checkComment(array $post, int $newsID, array $config): array
    {
        $checks = [
            'cKommentar' => 0,
            'nAnzahl'    => 0,
            'cEmail'     => 0,
            'cName'      => 0,
            'captcha'    => 0
        ];
        if (empty($post['cKommentar'])) {
            $checks['cKommentar'] = 1;
        } elseif (\mb_strlen($post['cKommentar']) > 1000) {
            $checks['cKommentar'] = 2;
        }
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && $newsID > 0) {
            $oNewsKommentar = Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS nAnzahl
                    FROM tnewskommentar
                    WHERE kNews = :nid
                        AND kKunde = :cid',
                ['nid' => $newsID, 'cid' => Frontend::getCustomer()->getID()],
                ReturnType::SINGLE_OBJECT
            );

            if ((int)$oNewsKommentar->nAnzahl > (int)$config['news']['news_kommentare_anzahlprobesucher']
                && (int)$config['news']['news_kommentare_anzahlprobesucher'] !== 0
            ) {
                $checks['nAnzahl'] = 1;
            }
            $post['cEmail'] = $_SESSION['Kunde']->cMail;
        } else {
            // Kunde ist nicht eingeloggt - Name prüfen
            if (empty($post['cName'])) {
                $checks['cName'] = 1;
            }
            if (empty($post['cEmail']) || Text::filterEmailAddress($post['cEmail']) === false) {
                $checks['cEmail'] = 1;
            }
        }
        if ((!isset($checks['cName']) || !$checks['cName']) && SimpleMail::checkBlacklist($post['cEmail'])) {
            $checks['cEmail'] = 2;
        }

        return $checks;
    }

    /**
     * @param array $checks
     * @return string
     */
    public static function getCommentErrors(array $checks): string
    {
        $msg = '';
        if ($checks['cKommentar'] > 0) {
            // Kommentarfeld ist leer
            if ($checks['cKommentar'] === 1) {
                $msg .= Shop::Lang()->get('newscommentMissingtext', 'errorMessages') . '<br />';
            } elseif ($checks['cKommentar'] === 2) {
                // Kommentar ist länger als 1000 Zeichen
                $msg .= Shop::Lang()->get('newscommentLongtext', 'errorMessages') . '<br />';
            }
        }
        // Kunde hat bereits einen Newskommentar zu der aktuellen News geschrieben
        if ($checks['nAnzahl'] === 1) {
            $msg .= Shop::Lang()->get('newscommentAlreadywritten', 'errorMessages') . '<br />';
        }
        // Kunde ist nicht eingeloggt und das Feld Name oder Email ist leer
        if ($checks['cName'] === 1 || $checks['cEmail'] === 1) {
            $msg .= Shop::Lang()->get('newscommentMissingnameemail', 'errorMessages') . '<br />';
        }
        // Emailadresse ist auf der Blacklist
        if ($checks['cEmail'] === 2) {
            $msg .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
        }

        return $msg;
    }

    /**
     * @param bool $activeOnly
     * @return stdClass
     */
    public static function getFilterSQL(bool $activeOnly = false): stdClass
    {
        $sql              = new stdClass();
        $sql->cSortSQL    = '';
        $sql->cDatumSQL   = '';
        $sql->cNewsKatSQL = '';
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case -1:
            default:
                $sql->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
                break;
            case 1: // Datum absteigend
                $sql->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
                break;
            case 2: // Datum aufsteigend
                $sql->cSortSQL = ' ORDER BY tnews.dGueltigVon';
                break;
            case 3: // Name a ... z
                $sql->cSortSQL = ' ORDER BY tnewssprache.title';
                break;
            case 4: // Name z ... a
                $sql->cSortSQL = ' ORDER BY tnewssprache.title DESC';
                break;
            case 5: // Anzahl Kommentare absteigend
                $sql->cSortSQL = ' ORDER BY nNewsKommentarAnzahl DESC';
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $sql->cSortSQL = ' ORDER BY nNewsKommentarAnzahl';
                break;
        }
        if ($_SESSION['NewsNaviFilter']->cDatum !== -1 && \mb_strlen($_SESSION['NewsNaviFilter']->cDatum) > 0) {
            $date = \explode('-', $_SESSION['NewsNaviFilter']->cDatum);
            if (\count($date) > 1) {
                [$nMonat, $nJahr] = $date;
                $sql->cDatumSQL   = " AND MONTH(tnews.dGueltigVon) = '" . (int)$nMonat . "' 
                                      AND YEAR(tnews.dGueltigVon) = '" . (int)$nJahr . "'";
            } else { //invalid date given/xss -> reset to -1
                $_SESSION['NewsNaviFilter']->cDatum = -1;
            }
        }
        if ($_SESSION['NewsNaviFilter']->nNewsKat > 0) {
            $sql->cNewsKatSQL = ' AND tnewskategorienews.kNewsKategorie = ' .
                (int)$_SESSION['NewsNaviFilter']->nNewsKat;
        }

        if ($activeOnly) {
            $sql->cNewsKatSQL .= ' JOIN tnewskategorie 
                                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                                    AND tnewskategorie.nAktiv = 1';
        }

        return $sql;
    }

    /**
     * @param object $sql
     * @return stdClass[]
     */
    private function getNewsDates($sql): array
    {
        $dateData = $this->db->query(
            'SELECT MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr
                FROM tnews 
                JOIN tnewskategorienews 
                    ON tnewskategorienews.kNews = tnews.kNews' . $sql->cNewsKatSQL . "
                JOIN tnewssprache
                    ON tnewssprache.kNews = tnews.kNews
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND (tnews.cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET('" . Frontend::getCustomerGroup()->getID() .
            "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    AND tnewssprache.languageID = " . Shop::getLanguageID() . '
                GROUP BY nJahr, nMonat
                ORDER BY dGueltigVon DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $dates    = [];
        foreach ($dateData as $date) {
            $item        = new stdClass();
            $item->cWert = $date->nMonat . '-' . $date->nJahr;
            $item->cName = self::mapDateName((int)$date->nMonat, (int)$date->nJahr, Shop::getLanguageCode());
            $dates[]     = $item;
        }

        return $dates;
    }

    /**
     * @param string|int $month
     * @param string|int $year
     * @param string $langCode
     * @return string
     */
    public static function mapDateName($month, $year, string $langCode): string
    {
        $month = (int)$month;
        $year  = (int)$year;
        $name  = '';
        // @todo: i18n!
        if ($langCode === 'ger') {
            switch ($month) {
                case 1:
                    return Shop::Lang()->get('january', 'news') . ',' . $year;
                case 2:
                    return Shop::Lang()->get('february', 'news') . ' ' . $year;
                case 3:
                    return Shop::Lang()->get('march', 'news') . ' ' . $year;
                case 4:
                    return Shop::Lang()->get('april', 'news') . ' ' . $year;
                case 5:
                    return Shop::Lang()->get('may', 'news') . ' ' . $year;
                case 6:
                    return Shop::Lang()->get('june', 'news') . ' ' . $year;
                case 7:
                    return Shop::Lang()->get('july', 'news') . ' ' . $year;
                case 8:
                    return Shop::Lang()->get('august', 'news') . ' ' . $year;
                case 9:
                    return Shop::Lang()->get('september', 'news') . ' ' . $year;
                case 10:
                    return Shop::Lang()->get('october', 'news') . ' ' . $year;
                case 11:
                    return Shop::Lang()->get('november', 'news') . ' ' . $year;
                case 12:
                    return Shop::Lang()->get('december', 'news') . ' ' . $year;
            }
        } else {
            $name .= \date('F', \mktime(0, 0, 0, $month, 1, $year)) . ', ' . $year;
        }

        return $name;
    }

    /**
     * @param int $newsItemID
     * @return array
     */
    public function getNewsCategories(int $newsItemID): array
    {
        $langID         = Shop::getLanguageID();
        $newsCategories = map(
            pluck($this->db->selectAll(
                'tnewskategorienews',
                'kNews',
                $newsItemID,
                'kNewsKategorie'
            ), 'kNewsKategorie'),
            static function ($e) {
                return (int)$e;
            }
        );

        return \count($newsCategories) > 0
            ? $this->db->query(
                'SELECT tnewskategorie.kNewsKategorie, t.languageID AS kSprache, t.name AS cName,
                t.description AS cBeschreibung, t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                tnewskategorie.cPreviewImage, tseo.cSeo,
                DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, \'%d.%m.%Y %H:%i\') AS dLetzteAktualisierung_de
                    FROM tnewskategorie
                    JOIN tnewskategoriesprache t 
                        ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    LEFT JOIN tseo 
                        ON tseo.cKey = \'kNewsKategorie\'
                        AND tseo.kKey = tnewskategorie.kNewsKategorie
                        AND tseo.kSprache = ' . $langID . '
                    WHERE t.languageID = ' . $langID . '
                        AND tnewskategorienews.kNewsKategorie IN (' . \implode(',', $newsCategories) . ')
                        AND tnewskategorie.nAktiv = 1
                    GROUP BY tnewskategorie.kNewsKategorie
                    ORDER BY tnewskategorie.nSort DESC',
                ReturnType::ARRAY_OF_OBJECTS
            )
            : [];
    }

    /**
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    /**
     * @param string $errorMsg
     */
    public function setErrorMsg(string $errorMsg): void
    {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @return string
     */
    public function getNoticeMsg(): string
    {
        return $this->noticeMsg;
    }

    /**
     * @param string $noticeMsg
     */
    public function setNoticeMsg(string $noticeMsg): void
    {
        $this->noticeMsg = $noticeMsg;
    }
}
