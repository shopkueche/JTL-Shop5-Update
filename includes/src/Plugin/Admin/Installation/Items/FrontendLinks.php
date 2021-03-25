<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\DB\ReturnType;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class FrontendLinks
 * @package JTL\Plugin\Admin\Installation\Items
 */
class FrontendLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['FrontendLink'][0]['Link'])
        && \is_array($this->baseNode['Install'][0]['FrontendLink'][0]['Link'])
            ? $this->baseNode['Install'][0]['FrontendLink'][0]['Link']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $pluginID    = $this->plugin->kPlugin;
        $oldPluginID = $this->oldPlugin === null ? 0 : $this->oldPlugin->getID();
        foreach ($this->getNode() as $i => $links) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (empty($links['LinkGroup'])) {
                $links['LinkGroup'] = 'hidden'; // linkgroup not set? default to 'hidden'
            }
            $linkGroupID = $this->getLinkGroup($links['LinkGroup']);
            if ($linkGroupID === 0) {
                return InstallCode::SQL_CANNOT_FIND_LINK_GROUP;
            }
            $linkID = $this->addLink($pluginID, $links);
            if ($linkID <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_LINK;
            }
            $this->db->insert(
                'tlinkgroupassociations',
                (object)['linkGroupID' => $linkGroupID, 'linkID' => $linkID]
            );
            $allLanguages    = LanguageHelper::getAllLanguages(2);
            $linkLang        = new stdClass();
            $linkLang->kLink = $linkID;
            $bLinkStandard   = false;
            $defaultLang     = new stdClass();
            $oldLink         = $oldPluginID === 0
                ? null
                : $this->db->select('tlink', 'kPlugin', $oldPluginID, 'cName', $links['Name']);
            if ($oldLink !== null) {
                $oldLinkGroup   = $this->db->select('tlinkgroupassociations', 'linkID', (int)$oldLink->kLink);
                $oldLinkGroupID = (int)($oldLinkGroup->linkGroupID ?? 0);
                if ($oldLinkGroupID > 0) {
                    $this->db->update(
                        'tlinkgroupassociations',
                        'linkID',
                        $linkID,
                        (object)['linkGroupID' => $oldLinkGroupID]
                    );
                }
            }
            foreach ($links['LinkLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    $linkLang->cISOSprache = \mb_convert_case($localized['iso'], \MB_CASE_LOWER);
                } elseif (\mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    $linkLang->cSeo             = Seo::checkSeo(Seo::getSeo($localized['Seo']));
                    $linkLang->cName            = $localized['Name'];
                    $linkLang->cTitle           = $localized['Title'];
                    $linkLang->cContent         = '';
                    $linkLang->cMetaTitle       = $localized['MetaTitle'];
                    $linkLang->cMetaKeywords    = $localized['MetaKeywords'];
                    $linkLang->cMetaDescription = $localized['MetaDescription'];
                    $this->db->insert('tlinksprache', $linkLang);
                    if (!$bLinkStandard) {
                        $defaultLang   = $linkLang;
                        $bLinkStandard = true;
                    }
                    if (($allLanguages[$linkLang->cISOSprache]->kSprache ?? 0) > 0) {
                        $or = isset($oldLink->kLink) ? (' OR kKey = ' . (int)$oldLink->kLink) : '';
                        $this->db->query(
                            "DELETE FROM tseo
                                WHERE cKey = 'kLink'
                                    AND (kKey = " . $linkID . $or . ')
                                    AND kSprache = ' . (int)$allLanguages[$linkLang->cISOSprache]->kSprache,
                            ReturnType::DEFAULT
                        );
                        $seo           = new stdClass();
                        $seo->cSeo     = Seo::checkSeo(Seo::getSeo($localized['Seo']));
                        $seo->cKey     = 'kLink';
                        $seo->kKey     = $linkID;
                        $seo->kSprache = $allLanguages[$linkLang->cISOSprache]->kSprache;
                        $this->db->insert('tseo', $seo);
                    }
                    if (isset($allLanguages[$linkLang->cISOSprache])) {
                        unset($allLanguages[$linkLang->cISOSprache]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            if (!$this->addHook($pluginID)) {
                return InstallCode::SQL_CANNOT_SAVE_HOOK;
            }
            $this->addMissingTranslations($allLanguages, $defaultLang, $linkID);
            $this->addLinkFile($pluginID, $linkID, $links);
        }

        return InstallCode::OK;
    }

    /**
     * Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
     *
     * @param array     $languages
     * @param stdClass $defaultLang
     * @param int       $linkID
     */
    private function addMissingTranslations(array $languages, stdClass $defaultLang, int $linkID): void
    {
        foreach ($languages as $language) {
            if ($language->kSprache <= 0) {
                continue;
            }
            $this->db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kLink', $linkID, (int)$language->kSprache]
            );
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo(Seo::getSeo($defaultLang->cSeo));
            $seo->cKey     = 'kLink';
            $seo->kKey     = $linkID;
            $seo->kSprache = $language->kSprache;
            $this->db->insert('tseo', $seo);
            $defaultLang->cSeo        = $seo->cSeo;
            $defaultLang->cISOSprache = $language->cISO;
            $this->db->insert('tlinksprache', $defaultLang);
        }
    }

    /**
     * @param int   $pluginID
     * @param int   $linkID
     * @param array $links
     * @return int
     */
    private function addLinkFile(int $pluginID, int $linkID, array $links): int
    {
        $linkFile                      = new stdClass();
        $linkFile->kPlugin             = $pluginID;
        $linkFile->kLink               = $linkID;
        $linkFile->cDatei              = $links['Filename'] ?? '';
        $linkFile->cTemplate           = $links['Template'] ?? '_DBNULL_';
        $linkFile->cFullscreenTemplate = $links['FullscreenTemplate'] ?? '_DBNULL_';

        return $this->db->insert('tpluginlinkdatei', $linkFile);
    }

    /**
     * @param int   $pluginID
     * @param array $links
     * @return int
     */
    private function addLink(int $pluginID, array $links): int
    {
        $link                     = new stdClass();
        $link->kPlugin            = $pluginID;
        $link->cName              = $links['Name'];
        $link->nLinkart           = \LINKTYP_PLUGIN;
        $link->cSichtbarNachLogin = $links['VisibleAfterLogin'] ?? 'N';
        $link->cDruckButton       = $links['PrintButton'] ?? 'N';
        $link->cNoFollow          = $links['NoFollow'] ?? 'N';
        $link->nSort              = \LINKTYP_PLUGIN;
        $link->bSSL               = (int)($links['SSL'] ?? 0);

        return $this->db->insert('tlink', $link);
    }

    /**
     * @param int $pluginID
     * @return int
     */
    private function addHook(int $pluginID): int
    {
        $hook             = new stdClass();
        $hook->kPlugin    = $pluginID;
        $hook->nHook      = \HOOK_SEITE_PAGE_IF_LINKART;
        $hook->cDateiname = \PLUGIN_SEITENHANDLER;

        return $this->db->insert('tpluginhook', $hook);
    }

    /**
     * @param string $name
     * @return int
     */
    private function getLinkGroup(string $name): int
    {
        $linkGroup = $this->db->select('tlinkgruppe', 'cName', $name);
        if ($linkGroup === null) {
            $linkGroup                = new stdClass();
            $linkGroup->cName         = $name;
            $linkGroup->cTemplatename = $name;
            $linkGroup->kLinkgruppe   = $this->db->insert('tlinkgruppe', $linkGroup);
        }

        return $linkGroup->kLinkgruppe > 0
            ? (int)$linkGroup->kLinkgruppe
            : 0;
    }
}
