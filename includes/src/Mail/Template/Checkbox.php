<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class Checkbox
 * @package JTL\Mail\Template
 */
class Checkbox extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_CHECKBOX_SHOPBETREIBER;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('oCheckBox', $data->oCheckBox)
               ->assign('oKunde', $data->oKunde)
               ->assign('cAnzeigeOrt', $data->cAnzeigeOrt)
               ->assign('oSprache', (object)['kSprache' => $this->languageID]);
        $subjectLineCustomer = empty($data->oKunde->cVorname) && empty($data->oKunde->cNachname)
            ? $data->oKunde->cMail
            : $data->oKunde->cVorname . ' ' . $data->oKunde->cNachname;
        $this->setSubject($data->oCheckBox->cName . ' - ' . $subjectLineCustomer);
    }
}
