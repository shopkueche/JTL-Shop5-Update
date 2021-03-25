<?php

namespace JTL\Extensions\SelectionWizard;

use JTL\Catalog\Category\Kategorie;
use JTL\DB\ReturnType;
use JTL\Filter\Items\Characteristic;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\SearchResults;
use JTL\Nice;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;

/**
 * Class Wizard
 * @package JTL\Extensions\SelectionWizard
 */
class Wizard
{
    /**
     * @var int
     */
    private $kAuswahlAssistentOrt = 0;

    /**
     * @var int
     */
    private $kAuswahlAssistentGruppe = 0;

    /**
     * @var string
     */
    private $cKey = '';

    /**
     * @var int
     */
    private $kKey = 0;

    /**
     * @var int
     */
    private $kSprache = 0;

    /**
     * @var string
     */
    private $cName = '';

    /**
     * @var string
     */
    private $cBeschreibung = '';

    /**
     * @var int
     */
    private $nAktiv = 0;

    /**
     * @var Question[]
     */
    private $questions = [];

    /**
     * @var Question[] - keys are kMerkmal
     */
    private $questionsAssoc = [];

    /**
     * @var int
     */
    private $nCurQuestion = 0;

    /**
     * @var array
     */
    private $selections = [];

    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private $config;

    /**
     * Wizard constructor.
     *
     * @param string $keyName
     * @param int    $id
     * @param int    $languageID
     * @param bool   $activeOnly
     */
    public function __construct(string $keyName, int $id, int $languageID = 0, bool $activeOnly = true)
    {
        $this->config = Shop::getSettings(\CONF_AUSWAHLASSISTENT)['auswahlassistent'];
        $languageID   = $languageID ?: Shop::getLanguageID();

        if ($id > 0
            && $languageID > 0
            && !empty($keyName)
            && Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_AUSWAHLASSISTENT)
        ) {
            $this->loadFromDB($keyName, $id, $languageID, $activeOnly);
        }
    }

    /**
     * @param string $keyName
     * @param int    $id
     * @param int    $languageID
     * @param bool   $activeOnly
     */
    private function loadFromDB(string $keyName, int $id, int $languageID, bool $activeOnly = true): void
    {
        $item = Shop::Container()->getDB()->queryPrepared(
            'SELECT *
                FROM tauswahlassistentort AS ao
                    JOIN tauswahlassistentgruppe AS ag
                        ON ao.kAuswahlAssistentGruppe = ag.kAuswahlAssistentGruppe
                            AND ao.cKey = :ckey
                            AND ao.kKey = :kkey
                            AND ag.kSprache = :ksprache' .
            ($activeOnly ? ' AND ag.nAktiv = 1' : ''),
            [
                'ckey'     => $keyName,
                'kkey'     => $id,
                'ksprache' => $languageID
            ],
            ReturnType::SINGLE_OBJECT
        );

        if ($item !== null && $item !== false) {
            foreach (\get_object_vars($item) as $name => $value) {
                $this->$name = $value;
            }

            $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
            $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
            $this->kKey                    = (int)$this->kKey;
            $this->kSprache                = (int)$this->kSprache;
            $this->nAktiv                  = (int)$this->nAktiv;

            $questionIDs = Shop::Container()->getDB()->queryPrepared(
                'SELECT kAuswahlAssistentFrage AS id
                    FROM tauswahlassistentfrage
                    WHERE kAuswahlAssistentGruppe = :groupID' .
                ($activeOnly ? ' AND nAktiv = 1 ' : ' ') .
                'ORDER BY nSort',
                ['groupID' => $this->kAuswahlAssistentGruppe],
                ReturnType::ARRAY_OF_OBJECTS
            );

            $this->questions = [];

            foreach ($questionIDs as $questionID) {
                $question                                  = new Question((int)$questionID->id);
                $this->questions[]                         = $question;
                $this->questionsAssoc[$question->kMerkmal] = $question;
            }
        }
    }

    /**
     * @param int $kWert
     * @return $this
     */
    public function setNextSelection(int $kWert): self
    {
        if ($this->nCurQuestion < \count($this->questions)) {
            $this->selections[] = $kWert;
            ++$this->nCurQuestion;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function filter(): self
    {
        $params = [];
        if ($this->cKey === \AUSWAHLASSISTENT_ORT_KATEGORIE) {
            $params['kKategorie'] = $this->kKey;
            if (\count($this->selections) > 0) {
                $params['MerkmalFilter_arr'] = $this->selections;
            }
        } elseif (\count($this->selections) > 0) {
            $params['kMerkmalWert'] = $this->selections[0];
            if (\count($this->selections) > 1) {
                $params['MerkmalFilter_arr'] = \array_slice($this->selections, 1);
            }
        }
        $productFilter   = Shop::buildProductFilter($params);
        $currentCategory = isset($params['kKategorie'])
            ? new Kategorie($params['kKategorie'])
            : null;
        $filterOptions   = (new SearchResults())->setFilterOptions(
            $productFilter,
            $currentCategory,
            true
        )->getCharacteristicFilterOptions();
        foreach ($filterOptions as $option) {
            /** @var Characteristic $option */
            if (\array_key_exists($option->getValue(), $this->questionsAssoc)) {
                $question                    = $this->questionsAssoc[$option->getValue()];
                $question->oWert_arr         = $option->getOptions();
                $question->nTotalResultCount = 0;
                foreach ($option->getOptions() as $oWert) {
                    $question->nTotalResultCount                           += $oWert->getCount();
                    $question->oWert_assoc[$oWert->getData('kMerkmalWert')] = $oWert;
                }
            }
        }
        $this->productFilter = $productFilter;

        return $this;
    }

    /**
     * Return the HTML for this selection wizard in its current state
     *
     * @param JTLSmarty $smarty
     * @return string
     */
    public function fetchForm($smarty): string
    {
        return $smarty->assign('AWA', $this)
            ->assign('Einstellungen', Shopsetting::getInstance()->getAll())
            ->fetch('selectionwizard/form.tpl');
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->kAuswahlAssistentOrt;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->kAuswahlAssistentGruppe;
    }

    /**
     * @return string
     */
    public function getLocationKeyName(): string
    {
        return $this->cKey;
    }

    /**
     * @return int
     */
    public function getLocationKeyId(): int
    {
        return $this->kKey;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return \preg_replace('/\s+/', ' ', \trim($this->cBeschreibung));
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->nAktiv === 1;
    }

    /**
     * @param int $nFrage
     * @return Question|null
     */
    public function getQuestion(int $nFrage): ?Question
    {
        return $this->questions[$nFrage] ?? null;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @return int
     */
    public function getQuestionCount(): int
    {
        return \count($this->questions);
    }

    /**
     * @return int
     */
    public function getCurQuestion(): int
    {
        return $this->nCurQuestion;
    }

    /**
     * @return array
     */
    public function getSelections(): array
    {
        return $this->selections;
    }

    /**
     * @param int $questionID
     * @return Option|mixed
     */
    public function getSelectedValue(int $questionID)
    {
        $question      = $this->questions[$questionID];
        $selectedValue = $this->selections[$questionID];

        return $question->oWert_assoc[$selectedValue];
    }

    /**
     * @return ProductFilter
     */
    public function getNaviFilter(): ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @return Option|null
     */
    public function getLastSelectedValue(): ?Option
    {
        $question      = \end($this->questions);
        $selectedValue = \end($this->selections);

        return $question->oWert_assoc[$selectedValue] ?? null;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getConf(string $name): ?string
    {
        return $this->config[$name];
    }

    /**
     * Tells whether the product wizard is enabled in the shop settings
     *
     * @return bool
     */
    public static function isRequired(): bool
    {
        return Shop::getSettings([\CONF_AUSWAHLASSISTENT])['auswahlassistent']['auswahlassistent_nutzen'] === 'Y';
    }

    /**
     * @param string             $keyName
     * @param int                $id
     * @param int                $languageID
     * @param JTLSmarty|null     $smarty
     * @param array              $selected
     * @param ProductFilter|null $pf
     * @return self|null
     */
    public static function startIfRequired(
        string $keyName,
        int $id,
        int $languageID = 0,
        $smarty = null,
        $selected = [],
        $pf = null
    ): ?self {
        // only start if enabled in the backend settings
        if (!self::isRequired()) {
            return null;
        }
        $filterCount = $pf !== null ? $pf->getFilterCount() : 0;
        // only start if no filters are already set
        if ($filterCount === 0) {
            $wizard = new self($keyName, $id, $languageID, true);
            // only start if the respective selection wizard group is enabled (active)
            if ($wizard->isActive()) {
                foreach (\array_filter($selected, '\is_numeric') as $kMerkmalWert) {
                    $wizard->setNextSelection($kMerkmalWert);
                }
                $wizard->filter();
                if ($smarty !== null) {
                    $smarty->assign('AWA', $wizard);
                }

                return $wizard;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getLinks(): array
    {
        return Shop::Container()->getDB()->selectAll('tlink', 'nLinkart', \LINKTYP_AUSWAHLASSISTENT);
    }
}
