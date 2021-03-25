<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class OrderCleared
 * @package JTL\Mail\Template
 */
class OrderCleared extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_BEZAHLT;

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
