<?php

namespace JTL\Link;

use Exception;
use JTL\DB\ReturnType;
use JTL\Helpers\FileSystem;
use JTL\MainModel;
use JTL\Shop;
use stdClass;

/**
 * Class LegacyLink
 * @package JTL\Link
 * @deprecated since 5.0.0
 */
class LegacyLink extends MainModel
{
    /**
     * @var int
     */
    public $kLink;

    /**
     * @var int
     */
    public $kVaterLink;

    /**
     * @var int
     */
    public $kLinkgruppe;

    /**
     * @var int
     */
    public $kPlugin;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var int
     */
    public $nLinkart;

    /**
     * @var string
     */
    public $cNoFollow;

    /**
     * @var string
     */
    public $cURL;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cSichtbarNachLogin;

    /**
     * deprecated
     *
     * @var string
     */
    public $cDruckButton;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $bSSL = 0;

    /**
     * @var int
     */
    public $bIsFluid = 0;

    /**
     * @var string
     */
    public $cIdentifier = '';

    /**
     * @var int
     */
    public $bIsActive = 1;

    /**
     * @var array
     */
    public $oSub_arr = [];

    /**
     * @var string
     */
    public $cISO;

    /**
     * @var int
     */
    public $kSprache = 0;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var int
     */
    public $nHTTPRedirectCode = 0;

    /**
     * @var bool
     */
    public $bHideContent = false;

    /**
     * @var int
     */
    public $nPluginStatus = 0;

    /**
     * @var string
     */
    public $cURLFull;

    /**
     * @var string
     */
    public $cURLFullSSL;

    /**
     * @var int
     */
    public $kSpezialSeite = 0;

    /**
     * @param int $kSpezialSeite
     * @return $this
     */
    public function setSpezialSeite(int $kSpezialSeite): self
    {
        $this->kSpezialSeite = $kSpezialSeite;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpezialSeite(): int
    {
        return (int)$this->kSpezialSeite;
    }

    /**
     * @return string|null
     */
    public function getURLFullSSL(): ?string
    {
        return $this->cURLFullSSL;
    }

    /**
     * @param string $cURLFullSSL
     * @return $this
     */
    public function setURLFullSSL($cURLFullSSL): self
    {
        $this->cURLFullSSL = $cURLFullSSL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURLFull(): ?string
    {
        return $this->cURLFull;
    }

    /**
     * @param string $cURLFull
     * @return $this
     */
    public function setURLFull($cURLFull): self
    {
        $this->cURLFull = $cURLFull;

        return $this;
    }

    /**
     * @param int $nPluginStatus
     * @return $this
     */
    public function setPluginStatus(int $nPluginStatus): self
    {
        $this->nPluginStatus = $nPluginStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getPluginStatus(): int
    {
        return (int)$this->nPluginStatus;
    }

    /**
     * @param string $cISO
     * @return $this
     */
    public function setISO($cISO): self
    {
        $this->cISO = $cISO;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getISO(): ?string
    {
        return $this->cISO;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
    }

    /**
     * @return int
     */
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @param string $cSeo
     * @return $this
     */
    public function setSeo($cSeo): self
    {
        $this->cSeo = $cSeo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSeo(): ?string
    {
        return $this->cSeo;
    }

    /**
     * @return int
     */
    public function getLink(): int
    {
        return (int)$this->kLink;
    }

    /**
     * @param int $kLink
     * @return $this
     */
    public function setLink(int $kLink): self
    {
        $this->kLink = $kLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getVaterLink(): int
    {
        return (int)$this->kVaterLink;
    }

    /**
     * @param int $kVaterLink
     * @return $this
     */
    public function setVaterLink(int $kVaterLink): self
    {
        $this->kVaterLink = $kVaterLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkgruppe(): int
    {
        return (int)$this->kLinkgruppe;
    }

    /**
     * @param int $kLinkgruppe
     * @return $this
     */
    public function setLinkgruppe(int $kLinkgruppe): self
    {
        $this->kLinkgruppe = $kLinkgruppe;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlugin(): int
    {
        return (int)$this->kPlugin;
    }

    /**
     * @param int $kPlugin
     * @return $this
     */
    public function setPlugin(int $kPlugin): self
    {
        $this->kPlugin = $kPlugin;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return int
     */
    public function getLinkart(): int
    {
        return (int)$this->nLinkart;
    }

    /**
     * @param int $nLinkart
     * @return $this
     */
    public function setLinkart(int $nLinkart): self
    {
        $this->nLinkart = $nLinkart;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNoFollow(): ?string
    {
        return $this->cNoFollow;
    }

    /**
     * @param string $cNoFollow
     * @return $this
     */
    public function setNoFollow($cNoFollow): self
    {
        $this->cNoFollow = $cNoFollow;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return $this
     */
    public function setURL($cURL): self
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKundengruppen(): ?string
    {
        return $this->cKundengruppen;
    }

    /**
     * @param string $cKundengruppen
     * @return $this
     */
    public function setKundengruppen($cKundengruppen): self
    {
        $this->cKundengruppen = $cKundengruppen;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSichtbarNachLogin(): ?string
    {
        return $this->cSichtbarNachLogin;
    }

    /**
     * @param string $cSichtbarNachLogin
     * @return $this
     */
    public function setSichtbarNachLogin($cSichtbarNachLogin): self
    {
        $this->cSichtbarNachLogin = $cSichtbarNachLogin;

        return $this;
    }

    /**
     * @deprecated since 4.0
     * @return string|null
     */
    public function getDruckButton(): ?string
    {
        return $this->cDruckButton;
    }

    /**
     * deprecated
     *
     * @param string $cDruckButton
     * @return $this
     */
    public function setDruckButton($cDruckButton): self
    {
        $this->cDruckButton = $cDruckButton;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return (int)$this->nSort;
    }

    /**
     * @param int $nSort
     * @return $this
     */
    public function setSort(int $nSort): self
    {
        $this->nSort = $nSort;

        return $this;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setSSL(int $mode): self
    {
        $this->bSSL = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSSL(): int
    {
        return (int)$this->bSSL;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setIsFluid(int $mode): self
    {
        $this->bIsFluid = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsFluid(): int
    {
        return (int)$this->bIsFluid;
    }

    /**
     * @param string $ident
     * @return $this
     */
    public function setIdentifier($ident): self
    {
        $this->cIdentifier = $ident;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->cIdentifier;
    }

    /**
     * @param null|int    $id
     * @param null|object $data
     * @param null|mixed  $option
     * @param null|int    $kLinkgruppe
     */
    public function __construct($id = null, $data = null, $option = null, int $kLinkgruppe = null)
    {
        if (\is_object($data)) {
            $this->loadObject($data);
        } elseif ($id !== null) {
            $this->load((int)$id, $data, $option, $kLinkgruppe);
        }
    }

    /**
     * @param int         $id
     * @param object|null $data
     * @param mixed|null  $option
     * @param int|null    $kLinkgruppe
     * @return $this
     */
    public function load($id, $data = null, $option = null, int $kLinkgruppe = null): self
    {
        if ($kLinkgruppe > 0) {
            $data = Shop::Container()->getDB()->queryPrepared(
                'SELECT tlink.* 
                    FROM tlink 
                    JOIN tlinkgroupassociations t 
                        ON tlink.kLink = t.linkID
                    WHERE tlink.kLink = :lid
                    AND t.linkGroupID = :lgid',
                ['lid' => (int)$id, 'lgid' => $kLinkgruppe],
                ReturnType::SINGLE_OBJECT
            );
        } else {
            $data = Shop::Container()->getDB()->select('tlink', 'kLink', (int)$id);
        }
        if (!empty($data->kLink)) {
            $this->loadObject($data);

            if ($option) {
                $this->oSub_arr = self::getSub($this->getLink(), $this->getLinkgruppe());
            }
        }

        return $this;
    }

    /**
     * @param int      $kVaterLink
     * @param int|null $kVaterLinkgruppe
     * @return null|array
     */
    public static function getSub(int $kVaterLink, int $kVaterLinkgruppe = null): ?array
    {
        if ($kVaterLink > 0) {
            if (!empty($kVaterLinkgruppe)) {
                $links = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tlink.* 
                        FROM tlink 
                        JOIN tlinkgroupassociations t 
                            ON tlink.kLink = t.linkID
                        WHERE tlink.kVaterLink = :parentID
                            AND t.linkGroupID = :lgid',
                    [
                        'parentID' => $kVaterLink,
                        'lgid'     => $kVaterLinkgruppe
                    ],
                    ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $links = Shop::Container()->getDB()->selectAll('tlink', 'kVaterLink', $kVaterLink);
            }
            foreach ($links as &$link) {
                $link = new self((int)$link->kLink, null, true, $kVaterLinkgruppe);
            }

            return $links;
        }

        return null;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins     = new stdClass();
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            $ins->kLink              = $this->kLink;
            $ins->kVaterLink         = $this->kVaterLink;
            $ins->kLinkgruppe        = $this->kLinkgruppe;
            $ins->kPlugin            = $this->kPlugin;
            $ins->cName              = $this->cName;
            $ins->nLinkart           = $this->nLinkart;
            $ins->cNoFollow          = $this->cNoFollow;
            $ins->cURL               = $this->cURL;
            $ins->cKundengruppen     = $this->cKundengruppen;
            $ins->bIsActive          = $this->bIsActive;
            $ins->cSichtbarNachLogin = $this->cSichtbarNachLogin;
            $ins->cDruckButton       = $this->cDruckButton;
            $ins->nSort              = $this->nSort;
            $ins->bSSL               = $this->bSSL;
            $ins->bIsFluid           = $this->bIsFluid;
            $ins->cIdentifier        = $this->cIdentifier;
        }

        $kPrim = Shop::Container()->getDB()->insert('tlink', $ins);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @throws Exception
     * @return int
     */
    public function update(): int
    {
        $sql     = 'UPDATE tlink SET ';
        $set     = [];
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            foreach ($members as $cMember) {
                $cMethod = 'get' . \mb_substr($cMember, 1);
                if (\method_exists($this, $cMethod)) {
                    $val    = $this->$cMethod();
                    $mValue = $val === null
                        ? 'NULL'
                        : ("'" . Shop::Container()->getDB()->realEscape($val) . "'");
                    $set[]  = $cMember . ' = ' . $mValue;
                }
            }

            $sql .= \implode(', ', $set);
            $sql .= ' WHERE kLink = ' . $this->getLink() . ' AND klinkgruppe = ' . $this->getLinkgruppe();

            return Shop::Container()->getDB()->query($sql, ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @param bool     $sub
     * @param int|null $linkGroupID
     * @return int
     */
    public function delete(bool $sub = true, int $linkGroupID = null): int
    {
        $rowCount = 0;
        if ($this->kLink > 0) {
            if (!empty($linkGroupID)) {
                $rowCount = Shop::Container()->getDB()->delete(
                    'tlink',
                    ['kLink', 'kLinkgruppe'],
                    [$this->getLink(), $linkGroupID]
                );
            } else {
                $rowCount = Shop::Container()->getDB()->delete('tlink', 'kLink', $this->getLink());
            }
            $links = Shop::Container()->getDB()->selectAll('tlink', 'kLink', $this->getLink());
            if (\count($links) === 0) {
                Shop::Container()->getDB()->delete('tlinksprache', 'kLink', $this->getLink());
                Shop::Container()->getDB()->delete('tseo', ['kKey', 'cKey'], [$this->getLink(), 'kLink']);

                $dir = \PFAD_ROOT . \PFAD_BILDER . \PFAD_LINKBILDER . $this->getLink();
                if (\is_dir($dir) && $this->getLink() > 0 && FileSystem::delDirRecursively($dir)) {
                    \rmdir($dir);
                }
            }

            if ($sub && \count($this->oSub_arr) > 0) {
                foreach ($this->oSub_arr as $oSub) {
                    $oSub->delete(true, $linkGroupID);
                }
            }
        }

        return $rowCount;
    }
}
