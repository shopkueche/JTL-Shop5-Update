<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class OrderShipped
 * @package JTL\Mail\Template
 */
class OrderShipped extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_VERSANDT;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('Bestellung', $data->tbestellung);
    }
}
