<?php declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearObjectCacheCommand
 * @package JTL\Console\Command\Cache
 */
class ClearObjectCacheCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('cache:clear')
            ->setDescription('Clear object cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        if (Shop::Container()->getCache()->flushAll()) {
            $io->success('Object cache cleared.');
        } else {
            $io->warning('Could not clear object cache.');
        }
    }
}
