<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc\renderers;

/**
 * Class TabsRenderer
 * @package scc\renderers
 */
class TabsRenderer extends BlockRenderer
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
        /** @var \JTL\Smarty\JTLSmarty|Smarty $tpl */
        $tpl    = $args[1];
        $smarty = $tpl->smarty;

        return $tpl->assign('params', $this->mergeParams($params))
                   ->assign('blockContent', $content)
                   ->assign('tabContents', $smarty->getTemplateVars('tabContents'))
                   ->assign('tabIDs', $smarty->getTemplateVars('tabIDs'))
                   ->assign('activeTabID', $smarty->getTemplateVars('activeTabID'))
                   ->assign('parentSmarty', $smarty)
                   ->fetch($this->component->getTemplate());
    }
}
