<?php declare(strict_types=1);

namespace JTL\Console;

use JTL\Console\Command\Backup\DatabaseCommand;
use JTL\Console\Command\Backup\FilesCommand;
use JTL\Console\Command\Cache\ClearObjectCacheCommand;
use JTL\Console\Command\Cache\DbesTmpCommand;
use JTL\Console\Command\Cache\DeleteFileCacheCommand;
use JTL\Console\Command\Cache\DeleteTemplateCacheCommand;
use JTL\Console\Command\Command;
use JTL\Console\Command\InstallCommand;
use JTL\Console\Command\Mailtemplates\ResetCommand;
use JTL\Console\Command\Migration\CreateCommand;
use JTL\Console\Command\Migration\InnodbUtf8Command;
use JTL\Console\Command\Migration\MigrateCommand;
use JTL\Console\Command\Migration\StatusCommand;
use JTL\Console\Command\Model\CreateCommand as CreateModelCommand;
use JTL\Console\Command\Plugin\CreateCommandCommand;
use JTL\Console\Command\Plugin\CreateMigrationCommand;
use JTL\Console\Command\Plugin\ValidateCommand;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Shop;
use JTL\XMLParser;
use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Application
 * @property ConsoleIO io
 * @property bool      devMode
 * @property bool      isInstalled
 * @package JTL\Console
 */
class Application extends BaseApplication
{
    /**
     * @var ConsoleIO
     */
    protected $io;

    /**
     * @var bool
     */
    protected $devMode = false;

    /**
     * @var bool
     */
    protected $isInstalled = false;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->devMode     = !empty(\APPLICATION_BUILD_SHA) && \APPLICATION_BUILD_SHA === '#DEV#' ?? false;
        $this->isInstalled = \defined('BLOWFISH_KEY');
        if ($this->isInstalled) {
            $cache = Shop::Container()->getCache();
            $cache->setJtlCacheConfig(
                Shop::Container()->getDB()->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
            );
        }

        parent::__construct('JTL-Shop', \APPLICATION_VERSION . ' - ' . ($this->devMode ? 'develop' : 'production'));
    }

    /**
     *
     */
    public function initPluginCommands(): void
    {
        if (!$this->isInstalled || \SAFE_MODE === true) {
            return;
        }
        $db              = Shop::Container()->getDB();
        $cache           = Shop::Container()->getCache();
        $parser          = new XMLParser();
        $validator       = new LegacyPluginValidator($db, $parser);
        $modernValidator = new PluginValidator($db, $parser);
        $listing         = new Listing($db, $cache, $validator, $modernValidator);
        $installed       = $listing->getInstalled();
        $sorted          = $listing->getAll($installed);
        $filteredPlugins = $sorted->filter(static function (ListingItem $i) {
            return $i->isShop5Compatible();
        });

        foreach ($filteredPlugins as $plugin) {
            /** @var ListingItem $plugin */
            if (!\is_dir($plugin->getPath() . '/Commands')) {
                continue;
            }
            $finder = Finder::create()
                ->ignoreVCS(false)
                ->ignoreDotFiles(false)
                ->in($plugin->getPath() . '/Commands');

            foreach ($finder->files() as $file) {
                /** @var SplFileInfo $file */
                $class = \sprintf(
                    'Plugin\\%s\\Commands\\%s',
                    $plugin->getDir(),
                    \str_replace('.' . $file->getExtension(), '', $file->getBasename())
                );
                if (!\class_exists($class)) {
                    throw new RuntimeException("Class '" . $class . "' does not exist");
                }

                $command = new $class();
                /** @var Command $command */
                $command->setName($plugin->getID() . ':' . $command->getName());
                $this->add($command);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->io = new ConsoleIO($input, $output, $this->getHelperSet());

        return parent::doRun($input, $output);
    }

    /**
     * @return ConsoleIO
     */
    public function getIO(): ConsoleIO
    {
        return $this->io;
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultCommands(): array
    {
        $cmds = parent::getDefaultCommands();

        if ($this->isInstalled) {
            $cmds[] = new MigrateCommand();
            $cmds[] = new StatusCommand();
            $cmds[] = new InnodbUtf8Command();
            $cmds[] = new DatabaseCommand();
            $cmds[] = new FilesCommand();
            $cmds[] = new DeleteTemplateCacheCommand();
            $cmds[] = new DeleteFileCacheCommand();
            $cmds[] = new DbesTmpCommand();
            $cmds[] = new ClearObjectCacheCommand();
            $cmds[] = new CreateModelCommand();
            $cmds[] = new ResetCommand();

            if ($this->devMode) {
                $cmds[] = new CreateCommand();
            }
            if (\PLUGIN_DEV_MODE === true) {
                $cmds[] = new CreateMigrationCommand();
                $cmds[] = new CreateCommandCommand();
                $cmds[] = new ValidateCommand();
            }
        } else {
            $cmds[] = new InstallCommand();
        }

        return $cmds;
    }

    /**
     * @return array
     */
    protected function createAdditionalStyles(): array
    {
        return [
            'plain'     => new OutputFormatterStyle(),
            'highlight' => new OutputFormatterStyle('red'),
            'warning'   => new OutputFormatterStyle('black', 'yellow'),
            'verbose'   => new OutputFormatterStyle('white', 'magenta'),

            'info_inverse'    => new OutputFormatterStyle('white', 'blue'),
            'comment_inverse' => new OutputFormatterStyle('black', 'yellow'),
            'success_inverse' => new OutputFormatterStyle('black', 'green'),
            'white_invert'    => new OutputFormatterStyle('black', 'white'),
        ];
    }
}
