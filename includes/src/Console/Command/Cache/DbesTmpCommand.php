<?php declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DbesTmpCommand
 * @package JTL\Console\Command\Cache
 */
class DbesTmpCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('cache:dbes:delete')
            ->setDescription('Delete dbeS cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $fs = new Filesystem(new Local(\PFAD_ROOT));
        if ($fs->deleteDir('dbeS/tmp/')) {
            $io->success('dbeS tmp cache deleted.');
        } else {
            $io->warning('Nothing to delete.');
        }
    }
}
