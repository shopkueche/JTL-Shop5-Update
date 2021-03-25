<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class Kaufabwicklung
 * @package Backend\Settings\Sections
 */
class WarenkorbKaufabwicklung extends Base
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty)
    {
        parent::__construct($db, $smarty);
        $this->hasSectionMarkup = true;
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getSectionMarkup(): string
    {
        return $this->smarty->fetch('tpl_inc/settingsection_warenkorb.tpl');
    }
}
