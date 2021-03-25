<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\Country\Country;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;

/**
 * Class CountryService
 * @package JTL\Services\JTL
 */
class CountryService implements CountryServiceInterface
{
    /**
     * @var Collection
     */
    private $countryList;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * CountryService constructor.
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->countryList = new Collection();
        $this->db          = $db;
        $this->cache       = $cache;
        $this->init();
    }

    public function init(): void
    {
        $cacheID = 'serviceCountryList';
        if (($countries = $this->cache->get($cacheID)) !== false) {
            $this->countryList = $countries;

            return;
        }
        $countries = $this->db->query('SELECT * FROM tland', ReturnType::ARRAY_OF_OBJECTS);
        foreach ($countries as $country) {
            $countryTMP = new Country($country->cISO);
            $countryTMP->setEU((int)$country->nEU)
                       ->setContinent($country->cKontinent)
                       ->setNameDE($country->cDeutsch)
                       ->setNameEN($country->cEnglisch);

            $this->getCountryList()->push($countryTMP);
        }

        $this->countryList = $this->getCountryList()->sortBy(static function (Country $country) {
            return $country->getName();
        });

        $this->cache->set($cacheID, $this->countryList, [\CACHING_GROUP_OBJECT]);
    }

    /**
     * @return Collection
     */
    public function getCountryList(): Collection
    {
        return $this->countryList;
    }

    /**
     * @param string $iso
     * @return Country
     */
    public function getCountry(string $iso): ?Country
    {
        return $this->getCountryList()->first(static function (Country $country) use ($iso) {
            return $country->getISO() === \strtoupper($iso);
        });
    }

    /**
     * @param array $ISOToFilter
     * @param bool $getAllIfEmpty
     * @return Collection
     */
    public function getFilteredCountryList(array $ISOToFilter, bool $getAllIfEmpty = false): Collection
    {
        if ($getAllIfEmpty && empty($ISOToFilter)) {
            return $this->getCountryList();
        }
        $filterItems = \array_map('\strtoupper', $ISOToFilter);

        return $this->getCountryList()->filter(static function (Country $country) use ($filterItems) {
            return \in_array($country->getISO(), $filterItems, true);
        });
    }

    /**
     * @param string $countryName
     * @return null|string
     */
    public function getIsoByCountryName(string $countryName): ?string
    {
        $name  = \strtolower($countryName);
        $match = $this->getCountryList()->first(static function (Country $country) use ($name) {
            foreach ($country->getNames() as $tmpName) {
                if (\strtolower($tmpName) === $name || $name === \strtolower($country->getNameDE())) {
                    return true;
                }
            }

            return false;
        });

        return $match ? $match->getISO() : null;
    }

    /**
     * @param bool $getEU - get all countries in EU and all countries in Europe not in EU
     * @param array|null $selectedCountries
     * @return array
     */
    public function getCountriesGroupedByContinent(bool $getEU = false, array $selectedCountries = []): array
    {
        $continentsTMP                = [];
        $continentsSelectedCountryTMP = [];
        $continents                   = [];
        foreach ($this->getCountryList() as $country) {
            $countrySelected                           = \in_array($country->getISO(), $selectedCountries, true);
            $continentsTMP[$country->getContinent()][] = $country;
            if ($countrySelected) {
                $continentsSelectedCountryTMP[$country->getContinent()][] = $country;
            }
            if ($getEU) {
                if ($country->isEU()) {
                    $continentsTMP[__('europeanUnion')][] = $country;
                    if ($countrySelected) {
                        $continentsSelectedCountryTMP[__('europeanUnion')][] = $country;
                    }
                } elseif ($country->getContinent() === __('Europa')) {
                    $continentsTMP[__('notEuropeanUnionEurope')][] = $country;
                    if ($countrySelected) {
                        $continentsSelectedCountryTMP[__('notEuropeanUnionEurope')][] = $country;
                    }
                }
            }
        }
        foreach ($continentsTMP as $continent => $countries) {
            $continents[] = (object)[
                'name'                   => $continent,
                'countries'              => $countries,
                'countriesCount'         => \count($countries),
                'countriesSelectedCount' => \count($continentsSelectedCountryTMP[$continent] ?? []),
                'sort'                   => $this->getContinentSort($continent)
            ];
        }
        \usort($continents, static function ($a, $b) {
            return $a->sort <=> $b->sort;
        });

        return $continents;
    }

    /**
     * @param string $continent
     * @return int
     */
    public function getContinentSort(string $continent): int
    {
        switch ($continent) {
            case __('Europa'):
                return 1;
            case __('europeanUnion'):
                return 2;
            case __('notEuropeanUnionEurope'):
                return 3;
            case __('Asien'):
                return 4;
            case __('Afrika'):
                return 5;
            case __('Nordamerika'):
                return 6;
            case __('Suedamerika'):
                return 7;
            case __('Ozeanien'):
                return 8;
            case __('Antarktis'):
                return 9;
            default:
                return 0;
        }
    }
}
