<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation;

use JTL\Plugin\Admin\Validation\Items\Author;
use JTL\Plugin\Admin\Validation\Items\Blueprints;
use JTL\Plugin\Admin\Validation\Items\Boxes;
use JTL\Plugin\Admin\Validation\Items\Checkboxes;
use JTL\Plugin\Admin\Validation\Items\Consent;
use JTL\Plugin\Admin\Validation\Items\DateCreated;
use JTL\Plugin\Admin\Validation\Items\Exports;
use JTL\Plugin\Admin\Validation\Items\ExtendedTemplates;
use JTL\Plugin\Admin\Validation\Items\ExtensionDir;
use JTL\Plugin\Admin\Validation\Items\FrontendLinks;
use JTL\Plugin\Admin\Validation\Items\Hooks;
use JTL\Plugin\Admin\Validation\Items\Installation;
use JTL\Plugin\Admin\Validation\Items\LicenceExtension;
use JTL\Plugin\Admin\Validation\Items\Localization;
use JTL\Plugin\Admin\Validation\Items\MailTemplates;
use JTL\Plugin\Admin\Validation\Items\Menus;
use JTL\Plugin\Admin\Validation\Items\Name;
use JTL\Plugin\Admin\Validation\Items\NoUninstaller;
use JTL\Plugin\Admin\Validation\Items\PaymentMethods;
use JTL\Plugin\Admin\Validation\Items\PluginID;
use JTL\Plugin\Admin\Validation\Items\Portlets;
use JTL\Plugin\Admin\Validation\Items\SemVer;
use JTL\Plugin\Admin\Validation\Items\Version;
use JTL\Plugin\Admin\Validation\Items\WidgetsExtension;
use JTL\Plugin\Admin\Validation\Items\XMLVersion;

/**
 * Class PluginValidationFactory
 * @package JTL\Plugin\Admin\Validation
 */
class PluginValidationFactory
{
    /**
     * @param array  $node
     * @param string $dir
     * @param string $version
     * @param string $pluginID
     * @return ValidationItemInterface[]
     */
    public function getValidations($node, $dir, $version, $pluginID): array
    {
        $validation   = [];
        $validation[] = new Version($node, $dir, $version, $pluginID);
        $validation[] = new Name($node, $dir, $version, $pluginID);
        $validation[] = new ExtensionDir($node, $dir, $version, $pluginID);
        $validation[] = new XMLVersion($node, $dir, $version, $pluginID);
        $validation[] = new DateCreated($node, $dir, $version, $pluginID);
        $validation[] = new PluginID($node, $dir, $version, $pluginID);
        $validation[] = new Installation($node, $dir, $version, $pluginID);
        $validation[] = new Consent($node, $dir, $version, $pluginID);
        $validation[] = new Author($node, $dir, $version, $pluginID);
        $validation[] = new LicenceExtension($node, $dir, $version, $pluginID);
        $validation[] = new Hooks($node, $dir, $version, $pluginID);
        $validation[] = new Menus($node, $dir, $version, $pluginID);
        $validation[] = new FrontendLinks($node, $dir, $version, $pluginID);
        $validation[] = new PaymentMethods($node, $dir, $version, $pluginID);
        $validation[] = new Portlets($node, $dir, $version, $pluginID);
        $validation[] = new Blueprints($node, $dir, $version, $pluginID);
        $validation[] = new Boxes($node, $dir, $version, $pluginID);
        $validation[] = new MailTemplates($node, $dir, $version, $pluginID);
        $validation[] = new Localization($node, $dir, $version, $pluginID);
        $validation[] = new Checkboxes($node, $dir, $version, $pluginID);
        $validation[] = new WidgetsExtension($node, $dir, $version, $pluginID);
        $validation[] = new Exports($node, $dir, $version, $pluginID);
        $validation[] = new ExtendedTemplates($node, $dir, $version, $pluginID);
        $validation[] = new NoUninstaller($node, $dir, $version, $pluginID);
        $validation[] = new SemVer($node, $dir, $version, $pluginID);

        return $validation;
    }
}
