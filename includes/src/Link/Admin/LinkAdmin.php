<?php declare(strict_types=1);

namespace JTL\Link\Admin;

use Illuminate\Support\Collection;
use JTL\Backend\Revision;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Link\LinkGroupCollection;
use JTL\Link\LinkGroupInterface;
use JTL\Link\LinkGroupList;
use JTL\Link\LinkInterface;
use JTL\Services\JTL\LinkService;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Shop;
use stdClass;
use function Functional\map;

/**
 * Class LinkAdmin
 * @package JTL\Link\Admin
 */
final class LinkAdmin
{
    public const ERROR_LINK_ALREADY_EXISTS = 1;

    public const ERROR_LINK_NOT_FOUND = 2;

    public const ERROR_LINK_GROUP_NOT_FOUND = 3;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * LinkAdmin constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @param int $linkType
     * @param int $linkID
     * @param array $customerGroups
     * @return bool
     */
    public static function isDuplicateSpecialLink(int $linkType, int $linkID, array $customerGroups): bool
    {
        $link = new Link(Shop::Container()->getDB());
        $link->setCustomerGroups($customerGroups);
        $link->setLinkType($linkType);
        $link->setID($linkID);

        return $link->hasDuplicateSpecialLink();
    }

    /**
     * @return LinkGroupCollection
     */
    public function getLinkGroups(): LinkGroupCollection
    {
        $ls  = new LinkService($this->db, $this->cache);
        $lgl = new LinkGroupList($this->db, $this->cache);
        $lgl->loadAll();
        $linkGroups = $lgl->getLinkGroups()->filter(static function (LinkGroupInterface $e) {
            return $e->isSpecial() === false || $e->getTemplate() === 'unassigned';
        });
        foreach ($linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $filtered = $this->buildNavigation($linkGroup, $ls);
            $linkGroup->setLinks($filtered);
        }

        return $linkGroups;
    }

    /**
     * @param LinkGroupInterface   $linkGroup
     * @param LinkServiceInterface $service
     * @param int                  $parentID
     * @return Collection
     * @former build_navigation_subs_admin()
     */
    private function buildNavigation(LinkGroupInterface $linkGroup, $service, int $parentID = 0): Collection
    {
        $news = new Collection();
        foreach ($linkGroup->getLinks() as $link) {
            $link->setLevel(\count($service->getParentIDs($link->getID())));
            /** @var LinkInterface $link */
            if ($link->getParent() !== $parentID) {
                continue;
            }
            $link->setChildLinks($this->buildNavigation($linkGroup, $service, $link->getID()));
            $news->push($link);
        }

        return $news;
    }

    /**
     * @param int   $id
     * @param array $post
     * @return stdClass
     */
    public function createOrUpdateLinkGroup(int $id, array $post): stdClass
    {
        $linkGroup                = new stdClass();
        $linkGroup->kLinkgruppe   = (int)$post['kLinkgruppe'];
        $linkGroup->cName         = $this->specialChars($post['cName']);
        $linkGroup->cTemplatename = $this->specialChars($post['cTemplatename']);

        if ($id === 0) {
            $groupID = $this->db->insert('tlinkgruppe', $linkGroup);
        } else {
            $groupID = (int)$post['kLinkgruppe'];
            $this->db->update('tlinkgruppe', 'kLinkgruppe', $groupID, $linkGroup);
        }
        $localized              = new stdClass();
        $localized->kLinkgruppe = $groupID;
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $localized->cISOSprache = $language->getIso();
            $localized->cName       = $linkGroup->cName;
            $idx                    = 'cName_' . $language->getIso();
            if (isset($post[$idx])) {
                $localized->cName = $this->specialChars($post[$idx]);
            }
            $this->db->delete(
                'tlinkgruppesprache',
                ['kLinkgruppe', 'cISOSprache'],
                [$groupID, $language->getIso()]
            );
            $this->db->insert('tlinkgruppesprache', $localized);
        }

        return $linkGroup;
    }

    /**
     * @return array
     */
    public function getLinkGroupCountForLinkIDs(): array
    {
        $assocCount             = $this->db->query(
            'SELECT tlink.kLink, COUNT(*) AS cnt 
                FROM tlink 
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                GROUP BY tlink.kLink
                HAVING COUNT(*) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $linkGroupCountByLinkID = [];
        foreach ($assocCount as $item) {
            $linkGroupCountByLinkID[(int)$item->kLink] = (int)$item->cnt;
        }

        return $linkGroupCountByLinkID;
    }

    /**
     * @param int $linkID
     * @param int $linkGroupID
     * @return int
     */
    public function removeLinkFromLinkGroup(int $linkID, int $linkGroupID): int
    {
        $link = (new Link($this->db))->load($linkID);
        foreach ($link->getChildLinks() as $childLink) {
            $this->removeLinkFromLinkGroup($childLink->getID(), $linkGroupID);
        }

        return $this->db->delete(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$linkGroupID, $linkID]
        );
    }

    /**
     * @param int $linkID
     * @param int $parentLinkID
     * @return bool|stdClass
     */
    public function updateParentID(int $linkID, int $parentLinkID)
    {
        $link       = $this->db->select('tlink', 'kLink', $linkID);
        $parentLink = $this->db->select('tlink', 'kLink', $parentLinkID);

        if (isset($link->kLink)
            && $link->kLink > 0
            && ((isset($parentLink->kLink) && $parentLink->kLink > 0) || $parentLinkID === 0)
        ) {
            $this->db->update('tlink', 'kLink', $linkID, (object)['kVaterLink' => $parentLinkID]);

            return $link;
        }

        return false;
    }

    /**
     * @param int $linkID
     * @return int
     */
    public function deleteLink(int $linkID): int
    {
        return $this->db->executeQueryPrepared(
            "DELETE tlink, tlinksprache, tseo, tlinkgroupassociations
                FROM tlink
                LEFT JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                LEFT JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = :lid
                WHERE tlink.kLink = :lid
                    OR tlink.reference = :lid",
            ['lid' => $linkID],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int  $linkGroupID
     * @param bool $names
     * @return array
     */
    public function getPreDeletionLinks(int $linkGroupID, bool $names = true): array
    {
        $links = $this->db->queryPrepared(
            'SELECT tlink.cName
                FROM tlink
                JOIN tlinkgroupassociations A
                    ON tlink.kLink = A.linkID
                JOIN tlinkgroupassociations B
                    ON A.linkID = B.linkID
                WHERE A.linkGroupID = :lgid
                GROUP BY A.linkID
                HAVING COUNT(A.linkID) > 1',
            ['lgid' => $linkGroupID],
            ReturnType::ARRAY_OF_OBJECTS
        );

        return $names === true
            ? map($links, static function ($l) {
                return $l->cName;
            })
            : $links;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getMissingLinkTranslations(int $id): array
    {
        return $this->db->queryPrepared(
            'SELECT tlink.*, tsprache.*
                FROM tlink
                JOIN tsprache
                LEFT JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                    AND tlinksprache.cISOSprache = tsprache.cISO
                LEFT JOIN tsprache t2
                    ON t2.cISO = tlinksprache.cISOSprache
                    AND t2.cISO = tsprache.cISO
                WHERE t2.cISO IS NULL
                    AND tlink.reference = 0
                    AND tlink.kLink = :lid',
            ['lid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return Collection
     */
    public function getUntranslatedPageIDs(): Collection
    {
        return $this->db->query(
            'SELECT DISTINCT tlink.kLink AS id
                FROM tlink
                JOIN tsprache
                LEFT JOIN tlinksprache loc
                    ON tlink.kLink = loc.kLink
                    AND loc.cISOSprache = tsprache.cISO
                LEFT JOIN tsprache t2
                    ON t2.cISO = loc.cISOSprache
                    AND t2.cISO = tsprache.cISO
                WHERE t2.cISO IS NULL
                    AND tlink.reference = 0',
            ReturnType::COLLECTION
        )->map(function (stdClass $e) {
            return (int)$e->id;
        });
    }

    /**
     * @param int $id
     * @return array
     */
    public function getMissingLinkGroupTranslations(int $id): array
    {
        return $this->db->queryPrepared(
            'SELECT tlinkgruppe.*, tsprache.* 
                FROM tlinkgruppe
                JOIN tsprache
                LEFT JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                    AND tlinkgruppesprache.cISOSprache = tsprache.cISO
                LEFT JOIN tsprache t2
                    ON t2.cISO = tlinkgruppesprache.cISOSprache
                    AND t2.cISO = tsprache.cISO
                WHERE t2.cISO IS NULL
                    AND tlinkgruppe.kLinkgruppe = :lgid',
            ['lgid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int $linkID
     * @param int $targetLinkGroupID
     * @return int|Link
     */
    public function createReference(int $linkID, int $targetLinkGroupID)
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        $targetLinkGroup = $this->db->select('tlinkgruppe', 'kLinkgruppe', $targetLinkGroupID);
        if (!isset($targetLinkGroup->kLinkgruppe) || $targetLinkGroup->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkID', 'linkGroupID'],
            [$linkID, $targetLinkGroupID]
        );
        if (!empty($exists)) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $ref            = new stdClass();
        $ref->kPlugin   = $link->getPluginID();
        $ref->nLinkart  = \LINKTYP_REFERENZ;
        $ref->reference = $link->getID();
        $ref->cName     = __('Referenz') . ' ' . $link->getID();
        $linkID         = $this->db->insert('tlink', $ref);

        $ins              = new stdClass();
        $ins->linkID      = $linkID;
        $ins->linkGroupID = $targetLinkGroupID;
        $this->db->insert('tlinkgroupassociations', $ins);
        $this->copyChildLinksToLinkGroup($link, $targetLinkGroupID);

        return $link;
    }

    /**
     * @param int $linkID
     * @param int $oldLinkGroupID
     * @param int $newLinkGroupID
     * @return int|Link
     */
    public function updateLinkGroup(int $linkID, int $oldLinkGroupID, int $newLinkGroupID)
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        $linkgruppe = $this->db->select('tlinkgruppe', 'kLinkgruppe', $newLinkGroupID);
        if (!isset($linkgruppe->kLinkgruppe) || $linkgruppe->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$newLinkGroupID, $link->getID()]
        );
        if (!empty($exists)) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $upd              = new stdClass();
        $upd->linkGroupID = $newLinkGroupID;
        $rows             = $this->db->update(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$oldLinkGroupID, $link->getID()],
            $upd
        );
        if ($rows === 0) {
            // previously unassigned link
            $upd              = new stdClass();
            $upd->linkGroupID = $newLinkGroupID;
            $upd->linkID      = $link->getID();
            $this->db->insert('tlinkgroupassociations', $upd);
        }
        unset($upd->linkID);
        $this->updateChildLinkGroups($link, $oldLinkGroupID, $newLinkGroupID);

        return $link;
    }

    /**
     * @param int $linkGroupID
     * @return int
     */
    public function deleteLinkGroup(int $linkGroupID): int
    {
        $linkIDs = $this->db->selectAll('tlinkgroupassociations', 'linkGroupID', $linkGroupID);
        foreach ($linkIDs as $linkID) {
            $this->deleteLink((int)$linkID->linkID);
        }
        $res = $this->db->delete('tlinkgruppe', 'kLinkgruppe', $linkGroupID);
        $this->db->delete('tlinkgruppesprache', 'kLinkgruppe', $linkGroupID);

        return $res;
    }

    /**
     * @param LinkInterface $link
     * @param int           $old
     * @param int           $new
     */
    private function updateChildLinkGroups(LinkInterface $link, int $old, int $new): void
    {
        $upd              = new stdClass();
        $upd->linkGroupID = $new;
        foreach ($link->getChildLinks() as $childLink) {
            if ($old < 0) {
                // previously unassigned
                $ins              = new stdClass();
                $ins->linkGroupID = $new;
                $ins->linkID      = $childLink->getID();
                $this->db->insert('tlinkgroupassociations', $ins);
            } else {
                $this->db->update(
                    'tlinkgroupassociations',
                    ['linkGroupID', 'linkID'],
                    [$old, $childLink->getID()],
                    $upd
                );
            }
            $this->updateChildLinkGroups($childLink, $old, $new);
        }
    }

    /**
     * @param LinkInterface $link
     * @param int           $linkGroupID
     */
    public function copyChildLinksToLinkGroup(LinkInterface $link, int $linkGroupID): void
    {
        $link->buildChildLinks();
        $ins              = new stdClass();
        $ins->linkGroupID = $linkGroupID;
        foreach ($link->getChildLinks() as $childLink) {
            $ins->linkID = $childLink->getID();
            $this->db->insert(
                'tlinkgroupassociations',
                $ins
            );
            $this->copyChildLinksToLinkGroup($childLink, $linkGroupID);
        }
    }

    /**
     * @param array $post
     * @return stdClass
     */
    private function createLinkData(array $post): stdClass
    {
        $link                     = new stdClass();
        $link->kLink              = (int)$post['kLink'];
        $link->kPlugin            = (int)$post['kPlugin'];
        $link->cName              = $this->specialChars($post['cName']);
        $link->nLinkart           = (int)$post['nLinkart'];
        $link->nSort              = !empty($post['nSort']) ? $post['nSort'] : 0;
        $link->bSSL               = (int)$post['bSSL'];
        $link->bIsActive          = 1;
        $link->cSichtbarNachLogin = 'N';
        $link->cNoFollow          = 'N';
        $link->cIdentifier        = $post['cIdentifier'];
        $link->bIsFluid           = (isset($post['bIsFluid']) && $post['bIsFluid'] === '1') ? 1 : 0;
        if (GeneralObject::isCountable('cKundengruppen', $post)) {
            $link->cKundengruppen = \implode(';', $post['cKundengruppen']) . ';';
            if (\in_array('-1', $post['cKundengruppen'], true)) {
                $link->cKundengruppen = '_DBNULL_';
            }
        }
        if (isset($post['bIsActive']) && (int)$post['bIsActive'] !== 1) {
            $link->bIsActive = 0;
        }
        if (isset($post['cSichtbarNachLogin']) && $post['cSichtbarNachLogin'] === 'Y') {
            $link->cSichtbarNachLogin = 'Y';
        }
        if (isset($post['cNoFollow']) && $post['cNoFollow'] === 'Y') {
            $link->cNoFollow = 'Y';
        }
        if ($link->nLinkart > 2 && isset($post['nSpezialseite']) && (int)$post['nSpezialseite'] > 0) {
            $link->nLinkart = (int)$post['nSpezialseite'];
        }

        return $link;
    }

    /**
     * @param array $post
     * @return Link
     */
    public function createOrUpdateLink(array $post): Link
    {
        $link = $this->createLinkData($post);
        if ($link->kLink === 0) {
            $kLink              = $this->db->insert('tlink', $link);
            $assoc              = new stdClass();
            $assoc->linkID      = $kLink;
            $assoc->linkGroupID = (int)$post['kLinkgruppe'];
            $this->db->insert('tlinkgroupassociations', $assoc);
        } else {
            $kLink    = $link->kLink;
            $revision = new Revision($this->db);
            $revision->addRevision('link', $kLink, true);
            $this->db->update('tlink', 'kLink', $kLink, $link);
        }
        $localized        = new stdClass();
        $localized->kLink = $kLink;
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $code                   = $language->getIso();
            $localized->cISOSprache = $code;
            $localized->cName       = $link->cName;
            $localized->cTitle      = '';
            $localized->cContent    = '';
            if (!empty($post['cName_' . $code])) {
                $localized->cName = $this->specialChars($post['cName_' . $code]);
            }
            if (!empty($post['cTitle_' . $code])) {
                $localized->cTitle = $this->specialChars($post['cTitle_' . $code]);
            }
            if (!empty($post['cContent_' . $code])) {
                $localized->cContent = $this->parseText($post['cContent_' . $code], $kLink);
            }
            $localized->cSeo = $localized->cName;
            if (!empty($post['cSeo_' . $code])) {
                $localized->cSeo = $post['cSeo_' . $code];
            }
            $localized->cMetaTitle = $localized->cTitle;
            $idx                   = 'cMetaTitle_' . $code;
            if (isset($post[$idx])) {
                $localized->cMetaTitle = $this->specialChars($post[$idx]);
            }
            $localized->cMetaKeywords    = $this->specialChars($post['cMetaKeywords_' . $code]);
            $localized->cMetaDescription = $this->specialChars($post['cMetaDescription_' . $code]);
            $this->db->delete('tlinksprache', ['kLink', 'cISOSprache'], [$kLink, $code]);
            $localized->cSeo = $link->nLinkart === \LINKTYP_EXTERNE_URL
                ? $localized->cSeo
                : Seo::getSeo($localized->cSeo);
            $this->db->insert('tlinksprache', $localized);
            $this->db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kLink', $localized->kLink, $language->getId()]
            );
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo($localized->cSeo);
            $seo->kKey     = $localized->kLink;
            $seo->cKey     = 'kLink';
            $seo->kSprache = $language->getId();
            $this->db->insert('tseo', $seo);
        }
        $linkInstance = new Link($this->db);
        $linkInstance->load($kLink);

        return $linkInstance;
    }

    /**
     * @param string $text
     * @param int    $linkID
     * @return mixed
     */
    private function parseText($text, int $linkID)
    {
        $uploadDir = \PFAD_ROOT . \PFAD_BILDER . \PFAD_LINKBILDER;
        $baseURL   = Shop::getURL() . '/' . \PFAD_BILDER . \PFAD_LINKBILDER;
        $images    = [];
        $sort      = [];
        if (\is_dir($uploadDir . $linkID)) {
            $dirHandle = \opendir($uploadDir . $linkID);
            while (($file = \readdir($dirHandle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $imageNumber          = (int)mb_substr(
                        \str_replace('Bild', '', $file),
                        0,
                        \mb_strpos(\str_replace('Bild', '', $file), '.')
                    );
                    $images[$imageNumber] = $file;
                    $sort[]               = $imageNumber;
                }
            }
        }
        \usort($sort, static function ($a, $b) {
            return $a <=> $b;
        });

        foreach ($sort as $no) {
            $text = \str_replace(
                '$#Bild' . $no . '#$',
                '<img src="' . $baseURL . $linkID . '/' . $images[$no] . '" />',
                $text
            );
        }

        return $text;
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        $this->cache->flushTags([\CACHING_GROUP_CORE]);
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);

        return true;
    }

    /**
     * @return Collection
     */
    public function getDuplicateSpecialLinks(): Collection
    {
        $group = Shop::Container()->getLinkService()->getAllLinkGroups()->getLinkgroupByTemplate('specialpages');
        if ($group === null) {
            return new Collection();
        }

        return $group->getLinks()->filter(static function (Link $link) {
            return $link->hasDuplicateSpecialLink();
        });
    }

    /**
     * @return Collection
     */
    public function getSpecialPageTypes(): Collection
    {
        return $this->db->query(
            'SELECT *
                FROM tspezialseite
                ORDER BY nSort',
            ReturnType::COLLECTION
        )->map(static function ($link) {
            $link->kSpezialseite = (int)$link->kSpezialseite;
            $link->kPlugin       = (int)$link->kPlugin;
            $link->nLinkart      = (int)$link->nLinkart;
            $link->nSort         = (int)$link->nSort;

            return $link;
        });
    }

    /**
     * @param int $linkID
     * @return int|string
     */
    public function getLastImageNumber(int $linkID)
    {
        $uploadDir = \PFAD_ROOT . \PFAD_BILDER . \PFAD_LINKBILDER;
        $images    = [];
        if (\is_dir($uploadDir . $linkID)) {
            $handle = \opendir($uploadDir . $linkID);
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $images[] = $file;
                }
            }
        }
        $max = 0;
        foreach ($images as $image) {
            $num = \mb_substr($image, 4, (\mb_strlen($image) - \mb_strpos($image, '.')) - 3);
            if ($num > $max) {
                $max = $num;
            }
        }

        return $max;
    }

    /**
     * @param string $text
     * @return string
     */
    private function specialChars(string $text): string
    {
        return \htmlspecialchars($text, \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
    }
}
