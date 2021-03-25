<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\renderers;

/**
 * Class CarouselRenderer
 * @package scc\renderers
 */
class CarouselRenderer extends BlockRenderer
{
    /**
     * @inheritdoc
     */
    public function render(array $params, ...$args): string
    {
        $content = $args[0];
        if ($content === null) {
            return '';
        }
        $tpl    = $args[1];
        $params = $this->mergeParams($params);
        $tpl->assign('params', $params)
            ->assign('blockContent', $content)
            ->assign('parentSmarty', $tpl->smarty);

        $res = $tpl->fetch($this->component->getTemplate());
        $tpl->smarty->assign('carouselSlides', 0);

        return $res;
    }
}
