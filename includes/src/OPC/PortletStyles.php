<?php declare(strict_types=1);

namespace JTL\OPC;

/**
 * Trait PortletStyles
 * @package JTL\OPC
 */
trait PortletStyles
{
    /**
     * @param bool $preview
     * @return string|null
     */
    final public function getCssFile($preview = false)
    {
        $cssPath = $this->getBasePath() . ($preview ? 'preview' : $this->getClass()) . '.css';
        $cssUrl  = $this->getBaseUrl() . ($preview ? 'preview' : $this->getClass()) . '.css';

        if (\file_exists($cssPath)) {
            return $cssUrl;
        }

        return null;
    }

    /**
     * @param bool $preview
     * @return array|string[]
     */
    final public function getCssFiles($preview = false)
    {
        $list = [];
        $file = $this->getCssFile($preview);

        if (!empty($file)) {
            $list[$file] = true;
        }

        $extras = $this->getExtraCssFiles();

        foreach ($extras as $extra) {
            $list[$extra] = true;
        }

        if (\in_array('styles', $this->getPropertyTabs(), true) && !$preview) {
            $url        = $this->getCommonResource('hidden-size.css');
            $list[$url] = true;
        }

        return $list;
    }

    /**
     * @return string[]
     */
    public function getExtraCssFiles()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getStylesPropertyDesc(): array
    {
        return [
            'background-color' => [
                'label'   => __('Background colour'),
                'type'    => InputType::COLOR,
                'default' => '',
                'width'   => 34,
            ],
            'color'            => [
                'type'    => InputType::COLOR,
                'label'   => __('Font colour'),
                'default' => '',
                'width'   => 34,
            ],
            'font-size'        => [
                'label'   => __('Font size'),
                'default' => '',
                'width'   => 34,
                'desc'    => __('cssNumericDesc'),
            ],
            'box-styles'       => [
                'type' => InputType::BOX_STYLES,
            ],
            'custom-class'     => [
                'type'        => InputType::TEXT,
                'label'       => __('Custom css class'),
                'default'     => '',
                'width'       => 100,
                'placeholder' => __('CustomCssClassPlaceholder'),
                'desc'        => __('CustomCssClassDesc'),
            ],
            'hidden-xs'        => [
                'type'  => InputType::CHECKBOX,
                'label' => __('Hidden on XS'),
                'width' => 25,
            ],
            'hidden-sm'        => [
                'type'  => InputType::CHECKBOX,
                'label' => __('Hidden on SM'),
                'width' => 25,
            ],
            'hidden-md'        => [
                'type'  => InputType::CHECKBOX,
                'label' => __('Hidden on MD'),
                'width' => 25,
            ],
            'hidden-lg'        => [
                'type'  => InputType::CHECKBOX,
                'label' => __('Hidden on LG'),
                'width' => 25,
            ],
        ];
    }
}
