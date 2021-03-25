<?php declare(strict_types=1);

namespace JTL\Smarty;

/**
 * Class JTLSmartyTemplateClass
 * @package JTL\Smarty
 */
class JTLSmartyTemplateClass extends \Smarty_Internal_Template
{
    /**
     * @inheritDoc
     */
    public function _subTemplateRender(
        $template,
        $cache_id,
        $compile_id,
        $caching,
        $cache_lifetime,
        $data,
        $scope,
        $forceTplCache,
        $uid = null,
        $content_func = null
    ) {
        return parent::_subTemplateRender(
            $this->smarty->getResourceName($template),
            $cache_id,
            $compile_id,
            $caching,
            $cache_lifetime,
            $data,
            $scope,
            $forceTplCache,
            $uid,
            $content_func
        );
    }

    /**
     * @inheritDoc
     */
    public function render($no_output_filter = true, $display = null)
    {
        if ($no_output_filter === false && $display !== 1) {
            $no_output_filter = true;
        }

        return parent::render($no_output_filter, $display);
    }
}
