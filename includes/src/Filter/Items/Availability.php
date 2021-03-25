<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\DB\ReturnType;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\InputType;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\Filter\Type;
use JTL\Shop;

/**
 * Class Availability
 * @package JTL\Filter\Items
 */
class Availability extends AbstractFilter
{
    /**
     * FilterAvailability constructor
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setType(Type::AND)
            ->setIcon('fa-truck')
            ->setUrlParam('availability')
            ->setInputType(InputType::BUTTON)
            ->setVisibility($this->getConfig('navigationsfilter')['allgemein_availabilityfilter_benutzen'])
            ->setFrontendName(Shop::isAdmin() ? __('filterAvailability') : Shop::Lang()->get('filterAvailability'));
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        return parent::setValue((int)$value);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = 'available';
        }
        if ($this->getValue() === 1) {
            $this->setName(Shop::Lang()->get('ampelGruen'));
        }

        return $this;
    }

    /**
     * @param string $seoString
     * @return int|null
     */
    public function mapSeoURL($seoString): ?int
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'taFA';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return "(
                    (taFA.cLagerBeachten = 'Y' AND taFA.fLagerbestand > 0)
                    OR taFA.cLagerBeachten = 'N'
                    OR taFA.cLagerKleinerNull = 'Y'
                )";
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return (new Join())
            ->setComment('join from FilterAvailability')
            ->setType('JOIN')
            ->setTable('tartikel AS taFA')
            ->setOn('tartikel.kArtikel = taFA.kArtikel')
            ->setOrigin(__CLASS__);
    }

    /**
     * @inheritDoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->options = [];
        if ((int)$this->getConfig()['global']['artikel_artikelanzeigefilter']
            !== \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_ALLE
        ) {
            return $this->options;
        }
        foreach ($this->getOptionData() as $option) {
            $active = $this->productFilter->filterOptionIsActive(
                $this->getClassName(),
                (int)$option->filterval
            );
            if ($active === true) {
                $this->hide();
            }
            $this->options[] = (new Option())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName(Shop::Lang()->get('ampelGruen'))
                ->setValue((int)$option->filterval)
                ->setCount((int)$option->nAnzahl)
                ->setURL(
                    $this->productFilter->getFilterURL()->getURL(
                        (new self($this->productFilter))->init((int)$option->filterval)
                    )
                )
                ->setIsActive($active);
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getOptionData(): array
    {
        $state = $this->productFilter->getCurrentStateData();
        $state->addJoin($this->getSQLJoin());
        $state->addCondition($this->getSQLCondition());
        $sql = new StateSQL();
        $sql->setJoins($state->getJoins());
        $sql->setSelect(['1 AS filterval']);
        $sql->setConditions($state->getConditions());
        $sql->setHaving($state->getHaving());
        $sql->setOrderBy('');

        return $this->productFilter->getDB()->query(
            'SELECT ssMerkmal.filterval, COUNT(*) AS nAnzahl
                FROM (' . $this->productFilter->getFilterSQL()->getBaseQuery($sql) . ' ) AS ssMerkmal
                GROUP BY ssMerkmal.filterval
                ORDER BY ssMerkmal.filterval ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
