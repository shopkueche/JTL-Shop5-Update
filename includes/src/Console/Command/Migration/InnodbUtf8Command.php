<?php declare(strict_types=1);

namespace JTL\Console\Command\Migration;

use JTL\Console\Command\Command;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Update\DBMigrationHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InnodbUtf8Command
 * @package JTL\Console\Command\Migration
 */
class InnodbUtf8Command extends Command
{
    /** @var array */
    private $excludeTables = [];

    /** @var int */
    private $errCounter = 0;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('migrate:innodbutf8')
            ->setDescription('Execute Innodb and UTF-8 migration');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db    = Shop::Container()->getDB();
        $table = DBMigrationHelper::getNextTableNeedMigration($this->excludeTables);
        while ($table !== null) {
            if ($this->errCounter > 20) {
                $this->getIO()->error('aborted due to too many errors');

                return;
            }

            $output->write('migrate ' . $table->TABLE_NAME . '... ');

            if (DBMigrationHelper::isTableInUse($table->TABLE_NAME)) {
                $table = $this->nextWithFailure($output, $table, false, 'already in use!');
                continue;
            }

            $this->prepareTable($table);
            $migrationState = DBMigrationHelper::isTableNeedMigration($table);
            if (($migrationState & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
                $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
                foreach ($fkSQLs->dropFK as $fkSQL) {
                    $db->executeQuery($fkSQL, ReturnType::DEFAULT);
                }
                $migrate = $db->executeQuery(
                    DBMigrationHelper::sqlMoveToInnoDB($table),
                    ReturnType::DEFAULT
                );
                foreach ($fkSQLs->createFK as $fkSQL) {
                    $db->executeQuery($fkSQL, ReturnType::DEFAULT);
                }

                if (!$migrate) {
                    $table = $this->nextWithFailure($output, $table);
                    continue;
                }
            }
            if (($migrationState & DBMigrationHelper::MIGRATE_COLUMN) !== DBMigrationHelper::MIGRATE_NONE) {
                $sql = DBMigrationHelper::sqlConvertUTF8($table);
                if (!empty($sql) && !$db->executeQuery($sql, ReturnType::DEFAULT)) {
                    $table = $this->nextWithFailure($output, $table);
                    continue;
                }
            }
            $this->releaseTable($table);
            $output->writeln('<info> ✔ </info>');

            $table = DBMigrationHelper::getNextTableNeedMigration($this->excludeTables);
        }

        if ($this->errCounter > 0) {
            $this->getIO()->warning('done with ' . $this->errCounter . ' errors');
        } else {
            $this->getIO()->success('all done');
        }
    }

    /**
     * @param \stdClass $table
     */
    private function prepareTable($table): void
    {
        if (\version_compare(DBMigrationHelper::getMySQLVersion()->innodb->version, '5.6', '<')) {
            // If MySQL version is lower than 5.6 use alternative lock method
            // and delete all fulltext indexes because these are not supported
            $db = Shop::Container()->getDB();
            $db->executeQuery(
                DBMigrationHelper::sqlAddLockInfo($table->TABLE_NAME),
                ReturnType::QUERYSINGLE
            );
            $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($table->TABLE_NAME);
            if ($fulltextIndizes) {
                foreach ($fulltextIndizes as $fulltextIndex) {
                    /** @noinspection SqlResolve */
                    $db->executeQuery(
                        'ALTER TABLE `' . $table->TABLE_NAME . '`
                            DROP KEY `' . $fulltextIndex->INDEX_NAME . '`',
                        ReturnType::QUERYSINGLE
                    );
                }
            }
        }
    }

    /**
     * @param \stdClass $table
     */
    private function releaseTable($table): void
    {
        if (\version_compare(DBMigrationHelper::getMySQLVersion()->innodb->version, '5.6', '<')) {
            $db = Shop::Container()->getDB();
            $db->executeQuery(
                DBMigrationHelper::sqlClearLockInfo($table),
                ReturnType::QUERYSINGLE
            );
        }
    }

    /**
     * @param OutputInterface $output
     * @param \stdClass $table
     * @param bool $releaseTable
     * @param string $msg
     * @return \stdClass|null
     */
    private function nextWithFailure(
        OutputInterface $output,
        $table,
        bool $releaseTable = true,
        string $msg = 'failure!'
    ):? \stdClass {
        $this->errCounter++;
        $output->writeln('<error>' . $msg . '</error>');
        $this->excludeTables[] = $table->TABLE_NAME;
        if ($releaseTable) {
            $this->releaseTable($table);
        }

        return DBMigrationHelper::getNextTableNeedMigration($this->excludeTables);
    }
}
