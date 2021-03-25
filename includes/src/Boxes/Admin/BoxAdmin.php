<?php declare(strict_types=1);

namespace JTL\Boxes\Admin;

use JTL\Boxes\Type;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mapper\PageTypeToPageNiceName;
use JTL\Shop;
use stdClass;
use function Functional\filter;
use function Functional\map;

/**
 * Class BoxAdmin
 * @package Boxes\Admin
 */
final class BoxAdmin
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $visibility;

    /**
     * @var array
     */
    private static $validPageTypes = [
        \PAGE_UNBEKANNT,
        \PAGE_ARTIKEL,
        \PAGE_ARTIKELLISTE,
        \PAGE_WARENKORB,
        \PAGE_MEINKONTO,
        \PAGE_KONTAKT,
        \PAGE_NEWS,
        \PAGE_NEWSLETTER,
        \PAGE_LOGIN,
        \PAGE_REGISTRIERUNG,
        \PAGE_BESTELLVORGANG,
        \PAGE_BEWERTUNG,
        \PAGE_PASSWORTVERGESSEN,
        \PAGE_WARTUNG,
        \PAGE_WUNSCHLISTE,
        \PAGE_VERGLEICHSLISTE,
        \PAGE_STARTSEITE,
        \PAGE_VERSAND,
        \PAGE_AGB,
        \PAGE_DATENSCHUTZ,
        \PAGE_LIVESUCHE,
        \PAGE_HERSTELLER,
        \PAGE_SITEMAP,
        \PAGE_GRATISGESCHENK,
        \PAGE_WRB,
        \PAGE_PLUGIN,
        \PAGE_NEWSLETTERARCHIV,
        \PAGE_EIGENE,
        \PAGE_AUSWAHLASSISTENT,
        \PAGE_BESTELLABSCHLUSS,
        \PAGE_404,
        \PAGE_BESTELLSTATUS,
        \PAGE_NEWSMONAT,
        \PAGE_NEWSDETAIL,
        \PAGE_NEWSKATEGORIE
    ];

    /**
     * BoxAdmin constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getValidPageTypes(): array
    {
        return self::$validPageTypes;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $affectedBoxes = map($this->db->queryPrepared(
            'SELECT kBox 
                FROM tboxen 
                WHERE kBox = :bid OR kContainer = :bid',
            ['bid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        ), static function ($e) {
            return (int)$e->kBox;
        });

        return \count($affectedBoxes) > 0
            && $this->db->query(
                'DELETE tboxen, tboxensichtbar, tboxsprache
                    FROM tboxen
                    LEFT JOIN tboxensichtbar USING (kBox)
                    LEFT JOIN tboxsprache USING (kBox)
                    WHERE tboxen.kBox IN (' . \implode(',', $affectedBoxes) . ')',
                ReturnType::AFFECTED_ROWS
            ) > 0;
    }

    /**
     * @param int $baseType
     * @return stdClass|null
     * @former holeVorlage()
     */
    private function getTemplate(int $baseType): ?stdClass
    {
        return $this->db->select('tboxvorlage', 'kBoxvorlage', $baseType);
    }

    /**
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return int
     * @former letzteSortierID()
     */
    private function getLastSortID(int $pageID, string $position = 'left', int $containerID = 0): int
    {
        $oBox = $this->db->queryPrepared(
            'SELECT tboxensichtbar.nSort, tboxen.ePosition
                FROM tboxensichtbar
                LEFT JOIN tboxen
                    ON tboxensichtbar.kBox = tboxen.kBox
                WHERE tboxensichtbar.kSeite = :pageid
                    AND tboxen.ePosition = :position
                    AND tboxen.kContainer = :containerid
                ORDER BY tboxensichtbar.nSort DESC
                LIMIT 1',
            [
                'pageid'      => $pageID,
                'position'    => $position,
                'containerid' => $containerID
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $oBox ? ++$oBox->nSort : 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @return mixed
     */
    public function getContent(int $boxID, string $isoCode = '')
    {

        return $isoCode !== ''
            ? $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode)
            : $this->db->selectAll('tboxsprache', 'kBox', $boxID);
    }

    /**
     * @param int $boxID
     * @return stdClass
     */
    public function getByID(int $boxID): stdClass
    {
        $oBox = $this->db->queryPrepared(
            'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = :bxid',
            ['bxid' => $boxID],
            ReturnType::SINGLE_OBJECT
        );

        $oBox->oSprache_arr      = ($oBox && ($oBox->eTyp === Type::TEXT || $oBox->eTyp === Type::CATBOX
                || $oBox->eTyp === Type::LINK))
            ? $this->getContent($boxID)
            : [];
        $oBox->kBox              = (int)$oBox->kBox;
        $oBox->kBoxvorlage       = (int)$oBox->kBoxvorlage;
        $oBox->supportsRevisions = $oBox->kBoxvorlage === \BOX_EIGENE_BOX_OHNE_RAHMEN
            || $oBox->kBoxvorlage === \BOX_EIGENE_BOX_MIT_RAHMEN;

        return $oBox;
    }

    /**
     * @param int    $baseID
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return bool
     */
    public function create(int $baseID, int $pageID, string $position = 'left', int $containerID = 0): bool
    {
        $validPageTypes    = $this->getValidPageTypes();
        $oBox              = new stdClass();
        $template          = $this->getTemplate($baseID);
        $oBox->cTitel      = $template === null
            ? ''
            : $template->cName;
        $oBox->kBoxvorlage = $baseID;
        $oBox->ePosition   = $position;
        $oBox->kContainer  = $containerID;
        $oBox->kCustomID   = (isset($template->kCustomID) && \is_numeric($template->kCustomID))
            ? (int)$template->kCustomID
            : 0;

        $boxID = $this->db->insert('tboxen', $oBox);
        if ($boxID) {
            $oBoxSichtbar       = new stdClass();
            $oBoxSichtbar->kBox = $boxID;
            foreach ($validPageTypes as $validPageType) {
                $oBoxSichtbar->nSort  = $this->getLastSortID($pageID, $position, $containerID);
                $oBoxSichtbar->kSeite = $validPageType;
                $oBoxSichtbar->bAktiv = ($pageID === $validPageType || $pageID === 0) ? 1 : 0;
                $this->db->insert('tboxensichtbar', $oBoxSichtbar);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int    $boxID
     * @param string $title
     * @param int    $customID
     * @return bool
     * @former bearbeiteBox()
     */
    public function update(int $boxID, $title, int $customID = 0): bool
    {
        $oBox            = new stdClass();
        $oBox->cTitel    = $title;
        $oBox->kCustomID = $customID;

        return $this->db->update('tboxen', 'kBox', $boxID, $oBox) >= 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @param string $title
     * @param string $content
     * @return bool
     * @former bearbeiteBoxSprache()
     */
    public function updateLanguage(int $boxID, string $isoCode, string $title, string $content): bool
    {
        $oBox = $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode);
        if (isset($oBox->kBox)) {
            $upd          = new stdClass();
            $upd->cTitel  = $title;
            $upd->cInhalt = $content;

            return $this->db->update('tboxsprache', ['kBox', 'cISO'], [$boxID, $isoCode], $upd) >= 0;
        }
        $_ins          = new stdClass();
        $_ins->kBox    = $boxID;
        $_ins->cISO    = $isoCode;
        $_ins->cTitel  = $title;
        $_ins->cInhalt = $content;

        return $this->db->insert('tboxsprache', $_ins) > 0;
    }

    /**
     * @param int      $pageID
     * @param string   $position
     * @param bool|int $show
     * @return bool
     * @former setzeBoxAnzeige()
     */
    public function setVisibility(int $pageID, string $position, $show): bool
    {
        $show           = (int)$show;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            foreach ($validPageTypes as $validPageType) {
                if (!$ok) {
                    break;
                }
                $ok = $this->db->queryPrepared(
                    'INSERT INTO tboxenanzeige 
                        SET bAnzeigen = :show, nSeite = :page, ePosition = :position
                        ON DUPLICATE KEY UPDATE
                          bAnzeigen = :show',
                    [
                        'show'     => $show,
                        'page'     => $validPageType,
                        'position' => $position
                    ],
                    ReturnType::DEFAULT
                );
            }

            return $ok !== 0;
        }

        return $this->db->queryPrepared(
            'INSERT INTO tboxenanzeige 
                SET bAnzeigen = :show, nSeite = :page, ePosition = :position
                ON DUPLICATE KEY UPDATE
                  bAnzeigen = :show',
            [
                'show'     => $show,
                'page'     => $pageID,
                'position' => $position
            ],
            ReturnType::DEFAULT
        ) !== 0;
    }

    /**
     * @param int      $boxID
     * @param int      $pageID
     * @param int      $sort
     * @param bool|int $active
     * @param bool     $ignore
     * @return bool
     * @former sortBox()
     */
    public function sort(int $boxID, int $pageID, int $sort, $active = true, $ignore = false): bool
    {
        $active         = (int)$active;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            foreach ($validPageTypes as $validPageType) {
                if (!$ok) {
                    break;
                }
                if ($ignore) {
                    $ok = $this->db->queryPrepared(
                        'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                        VALUES (:boxID, :validPageType, :sort, :active)
                        ON DUPLICATE KEY UPDATE
                          nSort = :sort',
                        [
                            'boxID'         => $boxID,
                            'validPageType' => $validPageType,
                            'sort'          => $sort,
                            'active'        => $active
                        ],
                        ReturnType::DEFAULT
                    );
                } else {
                    $ok = $this->db->queryPrepared(
                        'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                        VALUES (:boxID, :validPageType, :sort, :active)
                        ON DUPLICATE KEY UPDATE
                          nSort = :sort, bAktiv = :active',
                        [
                            'boxID'         => $boxID,
                            'validPageType' => $validPageType,
                            'sort'          => $sort,
                            'active'        => $active
                        ],
                        ReturnType::DEFAULT
                    );
                }
            }

            return $ok !== 0;
        }

        return $this->db->queryPrepared(
            'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                    VALUES (:boxID, :validPageType, :sort, :active)
                    ON DUPLICATE KEY UPDATE
                      nSort = :sort, bAktiv = :active',
            [
                    'boxID'         => $boxID,
                    'validPageType' => $pageID,
                    'sort'          => $sort,
                    'active'        => $active
                ],
            ReturnType::DEFAULT
        ) !== 0;
    }

    /**
     * @param int          $boxID
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $kSeite, $cFilter = ''): int
    {
        if (\is_array($cFilter)) {
            $cFilter = \array_unique($cFilter);
            $cFilter = \implode(',', $cFilter);
        }
        $upd          = new stdClass();
        $upd->cFilter = $cFilter;

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$boxID, $kSeite], $upd);
    }

    /**
     * @param int      $boxID
     * @param int      $pageID
     * @param bool|int $active
     * @return bool
     * @former aktiviereBox()
     */
    public function activate(int $boxID, int $pageID, $active = true): bool
    {
        $upd            = new stdClass();
        $upd->bAktiv    = (int)$active;
        $validPageTypes = $this->getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            foreach ($validPageTypes as $validPageType) {
                if (!$ok) {
                    break;
                }
                $ok = $this->db->update(
                    'tboxensichtbar',
                    ['kBox', 'kSeite'],
                    [$boxID, $validPageType],
                    $upd
                );
            }

            return $ok;
        }

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$boxID, 0], $upd) >= 0;
    }

    /**
     * @param int $pageID
     * @return array
     * @former holeVorlagen()
     */
    public function getTemplates(int $pageID = -1): array
    {
        $templates = [];
        $sql       = $pageID >= 0
            ? 'WHERE (cVerfuegbar = "' . $pageID . '" OR cVerfuegbar = "0")'
            : '';
        $data      = $this->db->query(
            'SELECT * 
                FROM tboxvorlage ' . $sql . ' 
                ORDER BY cVerfuegbar ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $template) {
            $id   = 0;
            $name = __('templateTypeTemplate');
            if ($template->eTyp === Type::TEXT) {
                $id   = 1;
                $name = __('templateTypeContent');
            } elseif ($template->eTyp === Type::LINK) {
                $id   = 2;
                $name = __('templateTypeLinkList');
            } elseif ($template->eTyp === Type::PLUGIN) {
                $id   = 3;
                $name = __('templateTypePlugin');
            } elseif ($template->eTyp === Type::CATBOX) {
                $id   = 4;
                $name = __('templateTypeCategory');
            } elseif ($template->eTyp === Type::EXTENSION) {
                $id   = 5;
                $name = __('templateTypeExtension');
            }

            if (!isset($templates[$id])) {
                $templates[$id]               = new stdClass();
                $templates[$id]->oVorlage_arr = [];
            }
            $template->cName                = __($template->cName);
            $templates[$id]->cName          = $name;
            $templates[$id]->oVorlage_arr[] = $template;
        }

        return $templates;
    }

    /**
     * @param int  $pageID
     * @param bool $global
     * @return array|bool
     * @former holeBoxAnzeige()
     */
    public function getVisibility(int $pageID, bool $global = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $visibility = [];
        $data       = $this->db->selectAll('tboxenanzeige', 'nSeite', $pageID);
        if (\count($data) > 0) {
            foreach ($data as $box) {
                $visibility[$box->ePosition] = (bool)$box->bAnzeigen;
            }
            $this->visibility = $visibility;

            return $visibility;
        }

        return $pageID !== 0 && $global
            ? $this->getVisibility(0)
            : false;
    }

    /**
     * @param string $position
     * @return array
     * @former holeContainer()
     */
    public function getContainer(string $position): array
    {
        return $this->db->selectAll(
            'tboxen',
            ['kBoxvorlage', 'ePosition'],
            [\BOX_CONTAINER, $position],
            'kBox',
            'kBox ASC'
        );
    }

    /**
     * @return array
     */
    public function getInvisibleBoxes(): array
    {
        $model      = Shop::Container()->getTemplateService()->getActiveTemplate();
        $unavailabe = filter($model->getBoxLayout(), static function ($e) {
            return $e === false;
        });
        $mapped     = map($unavailabe, static function ($e, $key) {
            return "'" . $key . "'";
        });

        return $this->db->query(
            'SELECT tboxen.*, tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate 
                FROM tboxen 
                    LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE ePosition IN (' . \implode(',', $mapped) . ') 
                    OR (kContainer > 0  AND kContainer NOT IN (SELECT kBox FROM tboxen))',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return array
     */
    public function getMappedValidPageTypes(): array
    {
        return map($this->getValidPageTypes(), static function ($pageID) {
            return [
                'pageID'   => $pageID,
                'pageName' => (new PageTypeToPageNiceName())->mapPageTypeToPageNiceName($pageID)
            ];
        });
    }
}
