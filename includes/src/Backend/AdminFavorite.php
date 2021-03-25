<?php

namespace JTL\Backend;

use Exception;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\URL;
use JTL\Shop;

/**
 * Class AdminFavorite
 * @package JTL\Backend
 */
class AdminFavorite
{
    /**
     * @var int
     */
    public $kAdminfav;

    /**
     * @var int
     */
    public $kAdminlogin;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cUrl;

    /**
     * @var int
     */
    public $nSort;

    /**
     * AdminFavorite constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        $obj = Shop::Container()->getDB()->select('tadminfavs', 'kAdminfav', $id);
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        \executeHook(\HOOK_ATTRIBUT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->kAdminfav);

        return Shop::Container()->getDB()->insert('tadminfavs', $obj);
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update('tadminfavs', 'kAdminfav', $obj->kAdminfav, $obj);
    }

    /**
     * @param int $adminID
     * @return array
     */
    public static function fetchAll(int $adminID): array
    {
        try {
            $favs = Shop::Container()->getDB()->selectAll(
                'tadminfavs',
                'kAdminlogin',
                $adminID,
                'kAdminfav, cTitel, cUrl',
                'nSort ASC'
            );
        } catch (Exception $e) {
            return [];
        }

        $favs = \is_array($favs) ? $favs : [];

        foreach ($favs as $fav) {
            $fav->bExtern = true;
            $fav->cAbsUrl = $fav->cUrl;
            if (\mb_strpos($fav->cUrl, 'http') !== 0) {
                $fav->bExtern = false;
                $fav->cAbsUrl = Shop::getURL() . '/' . $fav->cUrl;
            }
        }

        return $favs;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $url
     * @param int    $sort
     * @return bool
     */
    public static function add(int $id, $title, $url, int $sort = -1): bool
    {
        $urlHelper = new URL($url);
        $url       = \str_replace(
            [Shop::getURL(), Shop::getURL(true)],
            '',
            $urlHelper->normalize()
        );

        $url = \strip_tags($url);
        $url = \ltrim($url, '/');
        $url = \filter_var($url, \FILTER_SANITIZE_URL);

        if ($sort < 0) {
            $sort = \count(static::fetchAll($id));
        }

        $item = (object)[
            'kAdminlogin' => $id,
            'cTitel'      => $title,
            'cUrl'        => $url,
            'nSort'       => $sort
        ];

        if ($id > 0 && \mb_strlen($item->cTitel) > 0 && \mb_strlen($item->cUrl) > 0) {
            Shop::Container()->getDB()->insertRow('tadminfavs', $item);

            return true;
        }

        return false;
    }

    /**
     * @param int $adminID
     * @param int $favID
     */
    public static function remove($adminID, int $favID = 0): void
    {
        if ($favID > 0) {
            Shop::Container()->getDB()->delete('tadminfavs', ['kAdminfav', 'kAdminlogin'], [$favID, $adminID]);
        } else {
            Shop::Container()->getDB()->delete('tadminfavs', 'kAdminlogin', $adminID);
        }
    }
}
