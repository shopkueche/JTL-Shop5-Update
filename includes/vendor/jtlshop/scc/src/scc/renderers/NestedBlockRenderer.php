<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\renderers;

/**
 * Class NestedBlockRenderer
 * @package scc\renderers
 */
class NestedBlockRenderer extends BlockRenderer
{
    /**
     * @inheritdoc
     */
    public function render(array $params, ...$args): string
    {
        [$content, $tpl] = $args;
        if ($content === null) {
            $tpl->smarty->assign('parentBlockParams', $params);

            return '';
        }

        return $tpl->assign('params', $this->mergeParams($params))
                   ->assign('blockContent', $content)
                   ->assign('parentSmarty', $tpl->smarty)
                   ->fetch($this->component->getTemplate());
    }
}
