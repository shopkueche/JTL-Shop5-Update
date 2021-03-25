<?php declare(strict_types=1);

namespace JTL\Template;

use JTL\Shop;

/**
 * Class BootChecker
 * @package JTL\Template
 */
class BootChecker
{
    /**
     * @var BootstrapperInterface[]
     */
    private static $bootstrapper = [];

    /**
     * @param string|null     $dir
     * @param Model|null $model
     * @return BootstrapperInterface|null
     */
    public static function bootstrap(?string $dir, ?Model $model = null): ?BootstrapperInterface
    {
        if ($dir === null || ($model !== null && $model->getBootstrap() === 0)) {
            return null;
        }
        if (!isset(self::$bootstrapper[$dir])) {
            $class = \sprintf('Template\\%s\\%s', $dir, 'Bootstrap');
            if (!\class_exists($class)) {
                return null;
            }
            $bootstrapper = new $class(Shop::Container()->getDB(), Shop::Container()->getCache());
            if (!$bootstrapper instanceof BootstrapperInterface) {
                return null;
            }
            if ($model !== null) {
                $bootstrapper->setTemplate($model);
            }
            self::$bootstrapper[$dir] = $bootstrapper;
        }

        return self::$bootstrapper[$dir];
    }
}
