<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Helpers\URL;

/**
 * Class NewsItem
 * @package JTL\Sitemap\Items
 */
final class NewsItem extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_news_items'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->setImage($this->baseImageURL . $this->data->image);
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(URL::buildURL($this->data, \URLART_NEWS, true));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID((int)$data->kNews);
        $this->setLanguageData($languages, (int)$data->langID);
        $this->generateImage();
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->setChangeFreq(\FREQ_DAILY);
        $this->setPriority(\PRIO_HIGH);
        $this->setLastModificationTime(\date_format(\date_create($data->dlm), 'c'));
    }
}
