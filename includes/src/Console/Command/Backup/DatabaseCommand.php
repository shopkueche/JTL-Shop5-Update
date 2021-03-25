<?php declare(strict_types=1);

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use JTL\Shop;
use JTL\Update\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseCommand
 * @package JTL\Console\Command\Backup
 */
class DatabaseCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('backup:db')
            ->setAliases(['database:backup'])
            ->setDescription('Backup shop database')
            ->addOption('compress', 'c', InputOption::VALUE_NONE, 'Enable (gzip) compression');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io       = $this->getIO();
        $compress = $this->getOption('compress');
        $updater  = new Updater(Shop::Container()->getDB());
        try {
            $file = $updater->createSqlDumpFile($compress);
            $updater->createSqlDump($file, $compress);
            $io->success('SQL-Dump "' . $file . '" created.');
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
