<?php declare(strict_types=1);

namespace JTL\OPC\Portlets\Heading;

use JTL\OPC\InputType;
use JTL\OPC\Portlet;

/**
 * Class Heading
 * @package JTL\OPC\Portlets
 */
class Heading extends Portlet
{
    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-heading');
    }

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'level' => [
                'label'      => __('Level'),
                'type'       => InputType::SELECT,
                'options'    => [
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                    6 => '6',
                ],
                'default'    => '1',
                'required'   => true,
                'width'      => 17,
            ],
            'text'  => [
                'label'      => __('Text'),
                'type'       => InputType::TEXT,
                'default'    => __('Heading'),
                'width'      => 58,
            ],
            'align' => [
                'label'      => __('alignment'),
                'type'       => InputType::SELECT,
                'default'    => 'left',
                'options'    => [
                    'left'   => __('left'),
                    'center' => __('centered'),
                    'right'  => __('right'),
                ],
                'desc'       => __('alignmentDesc'),
                'width'      => 25,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }
}
