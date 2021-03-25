<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class ProductAvailable
 * @package JTL\Mail\Template
 */
class ProductAvailable extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('Benachrichtigung', $data->tverfuegbarkeitsbenachrichtigung)
               ->assign('Artikel', $data->tartikel);
    }
}
