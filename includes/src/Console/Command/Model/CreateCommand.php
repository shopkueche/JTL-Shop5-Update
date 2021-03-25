<?php declare(strict_types=1);

namespace JTL\Console\Command\Model;

use JTL\Console\Command\Command;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 * @package JTL\Console\Command\Model
 */
class CreateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('model:create')
            ->setDescription('Create a new model for given table    ')
            ->addArgument('table', InputArgument::REQUIRED, 'Name of the table for that model')
            ->addArgument('target-dir', InputArgument::OPTIONAL, 'Shop installation dir', \PFAD_ROOT)
            ->addArgument('author', InputArgument::OPTIONAL, 'Author');
    }


    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $tableName = \trim($input->getArgument('table') ?? '');
        $author    = \trim($input->getArgument('author') ?? '');
        $targetDir = \trim($input->getArgument('target-dir') ?? '');
        while ($tableName === null || \strlen($tableName) < 3) {
            $tableName = $this->getIO()->ask('Name of the table for that model');
        }
        $input->setArgument('table', $tableName);
        if (\strlen($author) < 2) {
            $author = $this->getIO()->ask('Author');
            $input->setArgument('author', $author);
        }
        if (\strlen($targetDir) < 2) {
            $targetDir = $this->getIO()->ask('target-dir');
            $input->setArgument('target-dir', $targetDir);
        }
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io        = $this->getIO();
        $targetDir = $input->getArgument('target-dir') ?? \PFAD_ROOT;
        $tableName = $input->getArgument('table');
        $author    = $input->getArgument('author') ?? '';
        $modelName = $this->writeDataModel($targetDir, $tableName, $author);

        $io->writeln("<info>Created DataModel:</info> <comment>'{$modelName}'</comment>");
    }

    /**
     * @param string      $targetDir
     * @param string      $table
     * @param string|null $author
     * @return string
     * @throws \SmartyException
     */
    protected function writeDataModel(string $targetDir, string $table, string $author = null): string
    {
        $smartyCli = Shop::Smarty(true, ContextType::CLI);
        $smartyCli->setCaching(JTLSmarty::CACHING_OFF);
        $datetime  = new \DateTime('NOW');
        $table     = \strtolower($table);
        $modelName = 'T' . \ucfirst(\ltrim($table, 't')) . 'Model';
        $relPath   = 'models';
        $modelPath = $relPath . DS . $modelName . '.php';
        $tableDesc = [];
        $attribs   = Shop::Container()->getDB()->getPDO()->query('DESCRIBE ' . $table);
        $dataType  = null;
        $typeMap   = [
            'bool|boolean',
            'int|tinyint|smallint|mediumint|integer|bigint|decimal|dec',
            'float|double',
            'DateTime|date|datetime|timestamp',
            'DateInterval|time',
            'string|year|char|varchar|tinytext|text|mediumtext|enum',
        ];

        foreach ($attribs as $attrib) {
            $dataType    = \preg_match('/^([a-zA-Z0-9]+)/', $attrib['Type'], $hits) ? $hits[1] : $attrib['Type'];
            $tableDesc[] = (object)[
                'name'         => "'{$attrib['Field']}'",
                'phpName'      => $attrib['Field'],
                'dataType'     => "'{$dataType}'",
                'phpType'      => \array_reduce($typeMap, static function ($carry, $item) use ($dataType) {
                    if (!isset($carry) && \preg_match("/{$item}/", $dataType)) {
                        $carry = \explode('|', $item, 2)[0];
                    }

                    return $carry;
                }),
                'default'      => isset($attrib['Default'])
                    ? "self::cast('{$attrib['Default']}', '{$dataType}')"
                    : 'null',
                'nullable'     => $attrib['Null'] === 'YES' ? 'true' : 'false',
                'isPrimaryKey' => $attrib['Key'] === 'PRI' ? 'true' : 'false',
            ];
        }
        $fileSystem = new Filesystem(new Local($targetDir));

        $content = $smartyCli->assign('tableName', $table)
            ->assign('modelName', $modelName)
            ->assign('author', $author)
            ->assign('created', $datetime->format(\DateTime::RSS))
            ->assign('tableDesc', $tableDesc)
            ->fetch(__DIR__ . '/Template/model.class.tpl');

        $fileSystem->put($modelPath, $content);

        return $modelPath;
    }
}
