<?php declare(strict_types=1);
/**
 * {$commandName}
 *
 * @author {$author}
 * @created {$created}
 */

namespace Plugin\{$pluginId}\Commands;

use JTL\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class {$commandName} extends Command
{
    protected function configure()
    {
        $this->setName('plugin:test')
            ->setDescription('Test description')
            ->addArgument('arg1', InputArgument::REQUIRED, 'Argument one');
    }

    /**
    * @param InputInterface $input
    * @param OutputInterface $output
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
