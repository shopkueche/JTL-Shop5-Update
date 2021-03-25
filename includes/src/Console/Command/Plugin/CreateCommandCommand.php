<?php declare(strict_types=1);

namespace JTL\Console\Command\Plugin;

use DateTime;
use Exception;
use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Plugin\Helper;
use JTL\Shop;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommandCommand
 * @package JTL\Console\Command\Plugin
 */
class CreateCommandCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('plugin:command:create')
            ->setDescription('Create new plugin command')
            ->addArgument('plugin-id', InputArgument::REQUIRED, 'Plugin id')
            ->addArgument('command-name', InputArgument::REQUIRED, 'Command name, like \'CronCommand\'')
            ->addArgument('author', InputArgument::REQUIRED, 'Author');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $pluginID    = \trim($input->getArgument('plugin-id') ?? '');
        $commandName = \trim($input->getArgument('command-name') ?? '');
        $author      = \trim($input->getArgument('author') ?? '');
        try {
            $commandPath = $this->createFile($pluginID, $commandName, $author);
            $output->writeln("<info>Created command:</info> <comment>'" . $commandPath . "'</comment>");
        } catch (Exception $e) {
            $this->getIO()->error($e->getMessage());

            return 1;
        }
    }

    /**
     * @param string $pluginID
     * @param string $commandName
     * @param string $author
     * @return string
     * @throws \SmartyException
     * @throws Exception
     */
    protected function createFile(string $pluginID, string $commandName, string $author): string
    {
        if (empty(Helper::getIDByPluginID($pluginID))) {
            throw new Exception('There is no plugin for the given dir name.');
        }

        $datetime      = new DateTime('NOW');
        $relPath       = 'plugins/' . $pluginID . '/Commands';
        $migrationPath = $relPath . '/' . $commandName . '.php';
        $fileSystem    = new Filesystem(new Local(\PFAD_ROOT));
        if (!$fileSystem->has($relPath)) {
            throw new Exception('Commands path doesn\'t exist!');
        }

        $content = Shop::Smarty()
            ->assign('commandName', $commandName)
            ->assign('author', $author)
            ->assign('created', $datetime->format(DateTime::RSS))
            ->assign('pluginId', $pluginID)
            ->fetch(\PFAD_ROOT . 'includes/src/Console/Command/Plugin/Template/command.class.tpl');

        $fileSystem->put($migrationPath, $content);

        return $migrationPath;
    }
}
