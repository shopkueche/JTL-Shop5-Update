<?php declare(strict_types=1);
/**
 * Defaults for new template settings in productlist
 *
 * @author fp
 * @created Tue, 13 Jun 2017 14:48:59 +0200
 */

use JTL\Template\Config;
use JTL\Template\TemplateServiceInterface;
use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shop;

/**
 * Class Migration_20170613144859
 */
class Migration_20170613144859 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Defaults for new template settings in productlist';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $template = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        $config   = new Config($template->getDir(), $this->getDB());
        $settings = Shop::getSettings([CONF_TEMPLATE])['template'];
        if ($template->getName() === 'Evo' || $template->getParent() === 'Evo') {
            if (!isset($settings['productlist']['variation_select_productlist'])) {
                $config->updateConfigInDB('productlist', 'variation_select_productlist', 'N');
            }
            if (!isset($settings['productlist']['variation_select_productlist'])) {
                $config->updateConfigInDB('productlist', 'quickview_productlist', 'N');
            }
            if (!isset($settings['productlist']['variation_select_productlist'])) {
                $config->updateConfigInDB('productlist', 'hover_productlist', 'N');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $currentTemplate = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        $this->execute("DELETE FROM ttemplateeinstellungen
            WHERE cTemplate = '" . $currentTemplate->getDir() . "' AND cSektion = 'productlist'");
    }
}
