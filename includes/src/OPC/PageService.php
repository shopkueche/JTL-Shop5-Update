<?php declare(strict_types=1);

namespace JTL\OPC;

use Exception;
use JTL\Backend\AdminIO;
use JTL\Helpers\Request;
use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * Class PageService
 * @package JTL\OPC
 */
class PageService
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var null|Service
     */
    protected $opc;

    /**
     * @var null|PageDB
     */
    protected $pageDB;

    /**
     * @var null|Locker
     */
    protected $locker;

    /**
     * @var null|Page
     */
    protected $curPage;

    /**
     * PageService constructor.
     * @param Service $opc
     * @param PageDB  $pageDB
     * @param Locker  $locker
     * @throws \SmartyException
     */
    public function __construct(Service $opc, PageDB $pageDB, Locker $locker)
    {
        $this->opc    = $opc;
        $this->pageDB = $pageDB;
        $this->locker = $locker;

        Shop::Smarty()->registerPlugin('function', 'opcMountPoint', [$this, 'renderMountPoint']);
    }

    /**
     * @return array list of the OPC service methods to be exposed for AJAX requests
     */
    public function getPageIOFunctionNames(): array
    {
        return [
            'getPageIOFunctionNames',
            'getRevisionList',
            'getDraft',
            'lockDraft',
            'unlockDraft',
            'getDraftPreview',
            'getDraftFinal',
            'getRevisionPreview',
            'publicateDraft',
            'saveDraft',
            'createPagePreview',
            'deleteDraft',
            'changeDraftName',
            'getDraftStatusHtml',
        ];
    }

    /**
     * @param AdminIO $io
     * @throws Exception
     */
    public function registerAdminIOFunctions(AdminIO $io): void
    {
        $adminAccount = $io->getAccount();

        if ($adminAccount === null) {
            throw new Exception('Admin account was not set on AdminIO.');
        }

        $this->adminName = $adminAccount->account()->cLogin;

        foreach ($this->getPageIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . \ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function renderMountPoint(array $params): string
    {
        $id          = $params['id'];
        $title       = $params['title'] ?? $id;
        $inContainer = $params['inContainer'] ?? true;
        $output      = '';

        if ($this->opc->isEditMode()) {
            $output = '<div class="opc-area opc-rootarea" data-area-id="' . $id . '" data-title="' . $title
                . '"></div>';
        } elseif (($areaList = $this->getCurPage()->getAreaList())->hasArea($id)) {
            $output = $areaList->getArea($id)->getFinalHtml($inContainer);
        }

        Shop::fire('shop.OPC.PageService.renderMountPoint', [
            'output' => &$output,
            'id'     => $id,
            'title'  => $title,
        ]);

        return $output;
    }

    /**
     * @param string $id
     * @return Page
     */
    public function createDraft($id): Page
    {
        return (new Page())->setId($id);
    }

    /**
     * @param int $key
     * @return Page
     * @throws Exception
     */
    public function getDraft(int $key): Page
    {
        return $this->pageDB->getDraft($key);
    }

    /**
     * @param int $revId
     * @return Page
     * @throws Exception
     */
    public function getRevision(int $revId): Page
    {
        return $this->pageDB->getRevision($revId);
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRevisionList(int $key): array
    {
        return $this->pageDB->getRevisionList($key);
    }

    /**
     * @param string $id
     * @return Page|null
     * @throws Exception
     */
    public function getPublicPage(string $id): ?Page
    {
        return $this->pageDB->getPublicPage($id);
    }

    /**
     * @return Page
     * @throws Exception
     */
    public function getCurPage(): Page
    {
        $isEditMode    = $this->opc->isEditMode();
        $isPreviewMode = $this->opc->isPreviewMode();
        $editedPageKey = $this->opc->getEditedPageKey();

        if ($this->curPage === null) {
            if ($this->opc->isOPCInstalled() === false) {
                $this->curPage = new Page();
            } elseif ($isEditMode && $editedPageKey > 0) {
                $this->curPage = $this->getDraft($editedPageKey);
            } elseif ($isPreviewMode) {
                $pageData      = $this->getPreviewPageData();
                $this->curPage = $this->createPageFromData($pageData);
            } else {
                $curPageUrl = $this->getCurPageUri();
                $curPageId  = $this->createCurrentPageId();

                if ($curPageId !== null) {
                    $this->curPage = $this->getPublicPage($curPageId) ?? new Page();
                    $this->curPage->setId($curPageId);
                    $this->curPage->setUrl($curPageUrl);
                } else {
                    $this->curPage = new Page();
                    $this->curPage->setIsModifiable(false);
                }
            }
        }

        return $this->curPage;
    }

    /**
     * @param int $langId
     * @return string
     */
    public function getCurPageUri(int $langId = 0): string
    {
        $uri = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'];
        if ($langId > 0) {
            $languages = $_SESSION['Sprachen'];
            foreach ($languages as $language) {
                if ($language->id === $langId) {
                    $uri = $language->url;
                    break;
                }
            }
        }
        $shopURLdata = \parse_url(Shop::getURL());
        $baseURLdata = \parse_url($uri);
        if (empty($shopURLdata['path'])) {
            $shopURLdata['path'] = '/';
        }

        if (!isset($baseURLdata['path'])) {
            return '/';
        }
        $result = \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path']));
        if (isset($baseURLdata['query'])) {
            $result .= '?' . $baseURLdata['query'];
        }
        $result = '/' . \ltrim($result, '/');

        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isCurPageModifiable()
    {
        return $this->getCurPage()->isModifiable();
    }

    /**
     * @param int $langId
     * @return string
     */
    public function createCurrentPageId(int $langId = 0): ?string
    {
        if ($langId === 0) {
            $langId = Shop::getLanguageID();
        }

        $params    = Shop::getParameters();
        $pageIdObj = (object)['lang' => $langId];

        if ($params['kKategorie'] > 0) {
            $pageIdObj->type = 'category';
            $pageIdObj->id   = $params['kKategorie'];
        } elseif ($params['kHersteller'] > 0) {
            $pageIdObj->type = 'manufacturer';
            $pageIdObj->id   = $params['kHersteller'];
        } elseif ($params['kArtikel'] > 0) {
            $pageIdObj->type = 'product';
            $pageIdObj->id   = $params['kArtikel'];
        } elseif ($params['kLink'] > 0) {
            if ($params['nLinkart'] === \LINKTYP_BESTELLVORGANG
                || $params['nLinkart'] === \LINKTYP_BESTELLABSCHLUSS
            ) {
                return null;
            }

            $pageIdObj->type = 'link';
            $pageIdObj->id   = $params['kLink'];
        } elseif ($params['kMerkmalWert'] > 0) {
            $pageIdObj->type = 'attrib';
            $pageIdObj->id   = $params['kMerkmalWert'];
        } elseif ($params['kSuchspecial'] > 0) {
            $pageIdObj->type = 'special';
            $pageIdObj->id   = $params['kSuchspecial'];
        } elseif ($params['kNews'] > 0) {
            $pageIdObj->type = 'news';
            $pageIdObj->id   = $params['kNews'];
        } elseif ($params['kNewsKategorie'] > 0) {
            $pageIdObj->type = 'newscat';
            $pageIdObj->id   = $params['kNewsKategorie'];
        } elseif (\mb_strlen($params['cSuche']) > 0) {
            $pageIdObj->type = 'search';
            $pageIdObj->id   = $params['cSuche'];
        } else {
            $pageIdObj->type = 'other';
            $pageIdObj->id   = \md5(\serialize($params));
        }

        if (!empty($params['MerkmalFilter'])) {
            $pageIdObj->attribs = $params['MerkmalFilter'];
        }

        if (!empty($params['cPreisspannenFilter'])) {
            $pageIdObj->range = $params['cPreisspannenFilter'];
        }

        if (!empty($params['kHerstellerFilter'])) {
            $pageIdObj->manufacturerFilter = $params['kHerstellerFilter'];
        }

        return \json_encode($pageIdObj);
    }

    /**
     * @param string $id
     * @return Page[]
     * @throws Exception
     */
    public function getDrafts(string $id): array
    {
        if ($this->opc->isOPCInstalled()) {
            $drafts         = $this->pageDB->getDrafts($id);
            $publicDraft    = $this->getPublicPage($id);
            $publicDraftKey = $publicDraft === null ? 0 : $publicDraft->getKey();
            \usort($drafts, static function ($a, $b) use ($publicDraftKey) {
                /**
                 * @var Page $a
                 * @var Page $b
                 */
                return $a->getStatus($publicDraftKey) - $b->getStatus($publicDraftKey);
            });
            return $drafts;
        }

        return [];
    }

    /**
     * @param int $key
     * @return string[]
     * @throws Exception
     */
    public function getDraftPreview(int $key): array
    {
        return $this->getDraft($key)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param int $key
     * @return array
     * @throws Exception
     */
    public function getDraftFinal(int $key): array
    {
        return $this->getDraft($key)->getAreaList()->getFinalHtml();
    }

    /**
     * @param int $revId
     * @return string[]
     * @throws Exception
     */
    public function getRevisionPreview(int $revId): array
    {
        return $this->getRevision($revId)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function saveDraft(array $data): void
    {
        $draft = $this->getDraft($data['key'])->deserialize($data);
        $this->pageDB->saveDraft($draft);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function publicateDraft(array $data): void
    {
        $page = (new Page())->deserialize($data);
        $this->pageDB->saveDraftPublicationStatus($page);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage(string $id): self
    {
        $this->pageDB->deletePage($id);

        return $this;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function deleteDraft(int $key): self
    {
        $this->pageDB->deleteDraft($key);

        return $this;
    }

    /**
     * @param int $key
     * @return int
     *      0 if the draft could be locked
     *      1 if it is still locked by some other user
     *      2 if the Shop has pending database updates
     * @throws Exception
     */
    public function lockDraft(int $key): int
    {
        if ($this->pageDB->shopHasPendingUpdates()) {
            return 2;
        }

        $draft = $this->getDraft($key);

        return $this->locker->lock($this->adminName, $draft) ? 0 : 1;
    }

    /**
     * @param int $key
     * @throws Exception
     */
    public function unlockDraft(int $key): void
    {
        $page = (new Page())->setKey($key);
        $this->locker->unlock($page);
    }

    /**
     * @param array $data
     * @return Page
     * @throws Exception
     */
    public function createPageFromData(array $data): Page
    {
        return (new Page())->deserialize($data);
    }

    /**
     * @param array $data
     * @return string[]
     * @throws Exception
     */
    public function createPagePreview(array $data): array
    {
        return $this->createPageFromData($data)->getAreaList()->getPreviewHtml();
    }

    /**
     * @return array
     */
    public function getPreviewPageData()
    {
        return \json_decode(Request::verifyGPDataString('pageData'), true);
    }

    /**
     * @param int    $draftKey
     * @param string $draftName
     * @throws Exception
     */
    public function changeDraftName(int $draftKey, string $draftName)
    {
        $this->pageDB->saveDraftName($draftKey, $draftName);
    }

    /**
     * @param int $draftKey
     * @return IOResponse
     * @throws \SmartyException
     */
    public function getDraftStatusHtml(int $draftKey): IOResponse
    {
        $draft    = $this->getDraft($draftKey);
        $smarty   = Shop::Smarty();
        $response = new IOResponse();

        $draftStatusHtml = $smarty
            ->assign('page', $draft)
            ->fetch(\PFAD_ROOT . \PFAD_ADMIN . 'opc/tpl/draftstatus.tpl');

        $response->assignDom('opcDraftStatus', 'innerHTML', $draftStatusHtml);

        return $response;
    }
}
