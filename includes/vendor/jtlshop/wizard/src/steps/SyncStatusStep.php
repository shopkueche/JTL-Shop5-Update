<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use JTL\Shop;
use jtl\Wizard\ShopWizard;
use stdClass;

/**
 * Class SyncStatusStep
 * @package jtl\Wizard\steps
 */
class SyncStatusStep extends Step implements IStep, \JsonSerializable
{
    /**
     * @var ShopWizard
     */
    private $wizard;

    /**
     * @var \stdClass|null
     */
    private $company;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var array
     */
    private $currencies = [];

    /**
     * @var bool
     */
    private $sync;

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Wawi-Abgleich';
    }

    /**
     * SyncStatusStep constructor.
     * @param ShopWizard $wizard
     */
    public function __construct($wizard)
    {
        $db            = Shop::Container()->getDB();
        $this->wizard  = $wizard;
        $this->company = $db->query('SELECT * FROM tfirma', 1);
        $this->sync    = $db->query('SELECT COUNT(*) AS cnt FROM tbrocken', 1)->cnt > 0;;

        if ($this->sync) {
            $this->groups     = $db->query('SELECT cName, cStandard FROM tkundengruppe', 2);
            $this->languages  = $db->query('SELECT cNameDeutsch, cShopStandard FROM tsprache', 2);
            $this->currencies = $db->query('SELECT cName, cStandard FROM twaehrung', 2);
        }
    }

    /**
     * @return array
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return array $currencies[]
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @return bool
     */
    public function isSync()
    {
        return $this->sync;
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {
        if ($jumpToNext === true) {
            $this->wizard->setStep(new GlobalSettingsStep($this->wizard));
        }
    }


    /**
     * @return stdClass
     */
    public function jsonSerialize()
    {
        $data = new stdClass();
        foreach (\get_object_vars($this) as $k => $v) {
            $data->$k = $v;
        }

        return $data;
    }
}
