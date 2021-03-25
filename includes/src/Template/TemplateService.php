<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Manager;
use JTL\License\Struct\ExpiredExsLicense;
use SimpleXMLElement;

/**
 * Class TemplateService
 * @package JTL\Template
 */
class TemplateService implements TemplateServiceInterface
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
     * @var Model
     */
    private $activeTemplate;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var string
     */
    private $cacheID = 'active_tpl';

    /**
     * TemplateService constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        if ($this->loaded === false) {
            $this->cache->set(
                $this->cacheID,
                $this->activeTemplate,
                $this->activeTemplate->getResources()->getCacheTags()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getActiveTemplate(bool $withLicense = true): Model
    {
        if ($this->activeTemplate === null) {
            $cacheID = 'active_tpl';
            if (($this->activeTemplate = $this->cache->get($cacheID)) === false) {
                $this->activeTemplate = $this->loadFull(['type' => 'standard'], $withLicense);
            } else {
                $this->loaded = true;
            }
        }
        $_SESSION['cTemplate'] = $this->activeTemplate->getTemplate();

        return $this->activeTemplate;
    }

    /**
     * @inheritDoc
     */
    public function loadFull(array $attributes, bool $withLicense = true): Model
    {
        try {
            $template = Model::loadByAttributes($attributes, $this->db);
        } catch (Exception $e) {
            $template = new Model($this->db);
            $template->setTemplate('no-template');
        }
        $reader    = new XMLReader();
        $tplXML    = $reader->getXML($template->getTemplate(), $template->getType() === 'admin');
        $parentXML = ($tplXML === null || empty($tplXML->Parent)) ? null : $reader->getXML((string)$tplXML->Parent);
        $dir       = $template->getTemplate();
        if ($dir === null || $tplXML === null) {
            $model = new Model($this->db);
            $model->setName($template->cTemplate ?? 'undefined');

            return $model;
        }
        $template = $this->mergeWithXML(
            $dir,
            $tplXML,
            $parentXML
        );
        if ($withLicense === true) {
            $manager    = new Manager($this->db, $this->cache);
            $exsLicense = $manager->getLicenseByItemID($template->getTemplate());
            if ($exsLicense === null && $template->getExsID() !== null) {
                $exsLicense = new ExpiredExsLicense();
                $exsLicense->initFromTemplateData($template);
            }
            $template->setExsLicense($exsLicense);
        }
        $template->setBoxLayout($this->getBoxLayout($tplXML, $parentXML));
        $template->setResources(new Resources($this->db, $tplXML, $parentXML));

        return $template;
    }

    /**
     * @param string                $dir
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXML
     * @return Model
     * @throws Exception
     */
    private function mergeWithXML(string $dir, SimpleXMLElement $xml, ?SimpleXMLElement $parentXML = null): Model
    {
        $template = Model::loadByAttributes(['cTemplate' => $dir], $this->db, Model::ON_NOTEXISTS_NEW);
        $template->setName(\trim((string)$xml->Name));
        $template->setDir($dir);
        $template->setAuthor(\trim((string)$xml->Author));
        $template->setUrl(\trim((string)$xml->URL));
        $template->setVersion(\trim((string)$xml->Version));
        $template->setFileVersion(\trim((string)$xml->Version));
        $template->setShopVersion(\trim((string)$xml->ShopVersion));
        $template->setPreview(\trim((string)$xml->Preview));
        $template->setDocumentationURL(\trim((string)$xml->DokuURL));
        $template->setIsChild(!empty($xml->Parent));
        $template->setParent(!empty($xml->Parent) ? \trim((string)$xml->Parent) : null);
        $template->setIsResponsive(empty($xml['isFullResponsive'])
            ? false
            : (\strtolower((string)$xml['isFullResponsive']) === 'true'));
        $template->setHasError(false);
        $template->setDescription(!empty($xml->Description) ? \trim((string)$xml->Description) : '');
        if ($parentXML !== null && !empty($xml->Parent)) {
            $parentConfig = $this->mergeWithXML((string)$xml->Parent, $parentXML);
            if ($parentConfig !== false) {
                $version = !empty($template->getVersion()) ? $template->getVersion() : $parentConfig->getVersion();
                $template->setVersion($version);
                $shopVersion = !empty($template->getShopVersion())
                    ? $template->getShopVersion()
                    : $parentConfig->getShopVersion();
                $template->setShopVersion($shopVersion);
            }
        }
        $version = $template->getVersion();
        if (empty($version)) {
            $template->setVersion($template->getShopVersion());
        }
        if (empty($template->getFileVersion())) {
            $template->setFileVersion($template->getVersion());
        }
        $template->setHasConfig(isset($xml->Settings->Section) || $template->isChild());
        if (\mb_strlen($template->getName()) === 0) {
            $template->setName($dir);
        }
        $config = new Config($template->getDir(), $this->db);
        $template->setConfig($config);

        return $template;
    }

    /**
     * @param SimpleXMLElement      $tplXML
     * @param SimpleXMLElement|null $parentXML
     * @return array
     */
    private function getBoxLayout(SimpleXMLElement $tplXML, ?SimpleXMLElement $parentXML = null): array
    {
        $items = [];
        foreach ([$tplXML, $parentXML] as $xml) {
            if ($xml === null || !isset($xml->Boxes) || \count($xml->Boxes) !== 1) {
                continue;
            }
            foreach ($xml->Boxes[0] as $item) {
                /** @var SimpleXMLElement $item */
                $attr                           = $item->attributes();
                $items[(string)$attr->Position] = (bool)(int)$attr->Available;
            }
        }

        return $items;
    }

    public function reset(): void
    {
        $this->activeTemplate = null;
    }
}
