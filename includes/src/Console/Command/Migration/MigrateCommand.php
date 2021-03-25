<?php declare(strict_types=1);

namespace JTL\Console\Command\Migration;

use Exception;
use JTL\Console\Command\Command;
use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\MigrationManager;
use JTL\Update\Updater;
use PDOException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateCommand
 * @package JTL\Console\Command\Migration
 */
class MigrateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('migrate')
            ->setDescription('Run the database migrations');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io                 = $this->getIO();
        $db                 = Shop::Container()->getDB();
        $updater            = new Updater($db);
        $manager            = new MigrationManager($db);
        $migrations         = $manager->getMigrations();
        $executedMigrations = $manager->getExecutedMigrations();
        $identifier         = \max(\array_merge($executedMigrations, \array_keys($migrations)));

        if (!$updater->hasMinUpdateVersion()) {
            Shop::Container()->getGetText()->setLanguage('en-GB')->loadAdminLocale('pages/dbupdater');
            $io->writeln('<error>' . $updater->getMinUpdateVersionError() . '</error>');
            return;
        }
        if (empty($executedMigrations) && empty($migrations)) {
            $io->writeln('<info>Nothing to migrate.</info>');
            return;
        }

        try {
            \ksort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getId() > $identifier) {
                    break;
                }
                if (!\in_array($migration->getId(), $executedMigrations)) {
                    $executedMigrations[] = $migration;
                    $manager->executeMigration($migration);
                    $io->writeln('<info>Migrated:</info> '
                        . $migration->getName() . ' '
                        . $migration->getDescription());
                }
            }
        } catch (PDOException $e) {
            [$code, , $message] = $e->errorInfo;
            $manager->log($migration, IMigration::UP, $code, $message);
            $io->error($e->getMessage());
        } catch (Exception $e) {
            $manager->log($migration, IMigration::UP, 'JTL01', $e->getMessage());
            $io->error($e->getMessage());
        }
    }
}
