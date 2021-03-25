<?php declare(strict_types=1);

namespace JTL\Sitemap\Items;

use JTL\Helpers\URL;

/**
 * Class Attribute
 * @package JTL\Sitemap\Items
 */
final class Attribute extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
        if ($this->config['sitemap']['sitemap_images_attributes'] !== 'Y') {
            return;
        }
        if (empty($this->data->image)) {
            return;
        }
        $this->setImage($this->baseImageURL . \PFAD_MERKMALWERTBILDER_NORMAL . $this->data->image);
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
        $this->setLocation(URL::buildURL($this->data, \URLART_SEITE));
    }

    /**
     * @inheritdoc
     */
    public function generateData($data, array $languages): void
    {
        $this->setData($data);
        $this->setPrimaryKeyID($data->kMerkmalWert);
        $this->setLanguageData($languages, $data->langID);
        $this->setLocation($this->baseURL . $data->cSeo);
        $this->generateImage();
        $this->setChangeFreq(\FREQ_WEEKLY);
        $this->setPriority(\PRIO_NORMAL);
        $this->setLastModificationTime(null);
    }
}
