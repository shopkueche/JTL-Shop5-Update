<?php declare(strict_types=1);

namespace JTL\Plugin\Admin;

use Parsedown;

/**
 * Class Extension
 */
class Markdown extends Parsedown
{
    /**
     * @var string|null
     */
    private $imagePrefixURL;

    /**
     * @param string $url
     */
    public function setImagePrefixURL(string $url): void
    {
        $this->imagePrefixURL = $url;
    }

    /**
     * @param $excerpt
     * @return array|void|null
     */
    protected function inlineImage($excerpt)
    {
        $image = parent::inlineImage($excerpt);
        if (!isset($image)) {
            return null;
        }
        if ($this->imagePrefixURL === null
            || \strpos($image['element']['attributes']['src'], 'http') === 0
            || \strpos($image['element']['attributes']['src'], '/') === 0
        ) {
            return $image;
        }

        $image['element']['attributes']['src'] = $this->imagePrefixURL . $image['element']['attributes']['src'];

        return $image;
    }
}
