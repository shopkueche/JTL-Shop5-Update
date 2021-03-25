<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\BootstrapperInterface;
use JTL\Plugin\InstallCode;

/**
 * Class Bootstrapper
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Bootstrapper extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $namespace = $this->getPluginID();
        $classFile = $this->getBaseDir() . \DIRECTORY_SEPARATOR . \PLUGIN_BOOTSTRAPPER;
        if (!\is_file($classFile)) {
            return InstallCode::OK;
        }
        $class = \sprintf('Plugin\\%s\\%s', $namespace, 'Bootstrap');

        require_once $classFile;

        if (!\class_exists($class)) {
            return InstallCode::MISSING_BOOTSTRAP_CLASS;
        }

        $bootstrapper = new $class((object)['cPluginID' => $namespace], null, null);

        return $bootstrapper instanceof BootstrapperInterface
            ? InstallCode::OK
            : InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION;
    }
}
