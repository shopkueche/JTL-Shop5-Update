<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class Plugin
 * @package JTL\Mail\Template
 */
class Plugin extends AbstractTemplate
{
    protected $id = 'core_jtl_plugin';

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('oPluginMail', $data);
    }
}
