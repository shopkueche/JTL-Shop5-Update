<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class ProductInquiry
 * @package JTL\Mail\Template
 */
class ProductInquiry extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_PRODUKTANFRAGE;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if (!empty($this->config['artikeldetails']['produktfrage_absender_name'])) {
            $this->setFromName($this->config['artikeldetails']['produktfrage_absender_name']);
        }
        if (!empty($this->config['artikeldetails']['produktfrage_absender_mail'])) {
            $this->setFromMail($this->config['artikeldetails']['produktfrage_absender_mail']);
        }
        if ($data === null) {
            return;
        }
        $smarty->assign('Nachricht', $data->tnachricht)
               ->assign('Artikel', $data->tartikel);
    }
}
