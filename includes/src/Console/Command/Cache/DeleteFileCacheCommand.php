<?php declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteFileCacheCommand
 * @package JTL\Console\Command\Cache
 */
class DeleteFileCacheCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('cache:file:delete')
            ->setDescription('Delete file cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $fs = new Filesystem(new Local(\PFAD_ROOT));
        if ($fs->deleteDir('/templates_c/filecache/')) {
            $io->success('File cache deleted.');
        } else {
            $io->warning('Nothing to delete.');
        }
    }
}
