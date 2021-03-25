<?php

namespace JTL\Plugin;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Mapper\PluginState;
use JTL\Shop;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class LegacyPlugin
 * @package JTL\Plugin,
 */
class LegacyPlugin extends PluginBC
{
    /**
     * @var array
     */
    public $oPluginHook_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellung_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellungConf_arr = [];

    /**
     * @var array
     */
    public $oPluginEinstellungAssoc_arr = [];

    /**
     * @var stdClass
     */
    public $oPluginUninstall;

    /**
     * @var string
     */
    public $cFehler = '';

    /**
     * @var int
     */
    public $nCalledHook;

    /**
     * LegacyPlugin constructor.
     * @param int  $id
     * @param bool $invalidateCache
     */
    public function __construct(int $id = 0, bool $invalidateCache = false)
    {
        if ($id > 0) {
            $this->loadFromDB($id, Shop::Container()->getDB(), Shop::Container()->getCache(), $invalidateCache);
        }
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getHookList(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getHookList();
    }

    /**
     * @param array $hookList
     * @return bool
     * @deprecated since 5.0.0
     */
    public static function setHookList(array $hookList): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::setHookList($hookList);
    }

    /**
     * @param int               $id
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param bool              $invalidate
     * @return null|$this
     */
    private function loadFromDB(int $id, DbInterface $db, JTLCacheInterface $cache, bool $invalidate = false): ?self
    {
        $loader = new LegacyPluginLoader($db, $cache);
        try {
            $res = $loader->setPlugin($this)->init($id, $invalidate);
            foreach (\get_object_vars($res) as $k => $v) {
                $this->$k = $v;
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                       = new stdClass();
        $obj->kPlugin              = $this->getID();
        $obj->cName                = $this->getMeta()->getName();
        $obj->cBeschreibung        = $this->getMeta()->getDescription();
        $obj->cAutor               = $this->getMeta()->getAuthor();
        $obj->cURL                 = $this->getMeta()->getURL();
        $obj->cVerzeichnis         = $this->getPaths()->getBaseDir();
        $obj->cFehler              = $this->cFehler;
        $obj->cLizenz              = $this->getLicense()->getKey();
        $obj->cLizenzKlasse        = $this->getLicense()->getClass();
        $obj->cLizenzKlasseName    = $this->getLicense()->getClassName();
        $obj->nStatus              = $this->getState();
        $obj->nVersion             = $this->getMeta()->getVersion();
        $obj->nPrio                = $this->getPriority();
        $obj->dZuletztAktualisiert = $this->getMeta()->getDateLastUpdate()->format('d.m.Y H:i');
        $obj->dInstalliert         = $this->getMeta()->getDateInstalled()->format('d.m.Y H:i');
        $obj->bBootstrap           = $this->isBootstrap() ? 1 : 0;

        return Shop::Container()->getDB()->update('tplugin', 'kPlugin', $obj->kPlugin, $obj);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function getConf(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @param string $pluginID
     * @return null|PluginInterface
     * @deprecated since 5.0.0
     */
    public static function getPluginById(string $pluginID): ?PluginInterface
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getPluginById($pluginID);
    }

    /**
     * @inheritdoc
     */
    public function getCurrentVersion(): Version
    {
        $path = \PFAD_ROOT . \PFAD_PLUGIN . $this->getPaths()->getBaseDir();
        if (!\is_dir($path) || !\file_exists($path . '/' . \PLUGIN_INFO_FILE)) {
            return Version::parse('0.0.0');
        }
        $parser  = new XMLParser();
        $xml     = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);
        $version = \count($xml['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;

        return Version::parse($xml['jtlshop3plugin'][0]['Install'][0]['Version'][$version . ' attr']['nr']);
    }

    /**
     * @param int $state
     * @return string
     * @deprecated since 5.0.0
     */
    public function mapPluginStatus(int $state): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $mapper = new PluginState();

        return $mapper->map($state);
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getTemplatePaths(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Helper::getTemplatePaths();
    }
}
