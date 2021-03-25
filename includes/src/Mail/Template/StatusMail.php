<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class StatusMail
 * @package JTL\Mail\Template
 */
class StatusMail extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_STATUSEMAIL;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $data->mail->toName = $data->tfirma->cName . ' ' . $data->cIntervall;
        $this->setSubject($data->tfirma->cName . ' ' . $data->cIntervall);
        $smarty->assign('oMailObjekt', $data);
    }
}
