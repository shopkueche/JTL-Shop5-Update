<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Helpers\URL;

/**
 * Class LiveSearch
 * @package JTL\Sitemap\Items
 */
final class LiveSearch extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(URL::buildURL($this->data, \URLART_SEITE, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kSuchanfrage);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
