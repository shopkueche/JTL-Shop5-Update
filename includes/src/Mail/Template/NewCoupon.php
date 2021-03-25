<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\Checkout\Kupon;
use JTL\Smarty\JTLSmarty;

/**
 * Class NewCoupon
 * @package JTL\Mail\Template
 */
class NewCoupon extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_KUPON;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('Kupon', $data->tkupon)
               ->assign('couponTypes', Kupon::getCouponTypes());
    }
}
