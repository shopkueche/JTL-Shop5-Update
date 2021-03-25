<?php declare(strict_types=1);

namespace JTL\Console\Command\Plugin;

use JTL\Console\Command\Command;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use JTL\XMLParser;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ValidateCommand
 * @package JTL\Console\Command\Plugin
 */
class ValidateCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('plugin:validate')
            ->setDescription('Validate available plugin')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('plugin-dir', null, InputOption::VALUE_REQUIRED, 'Plugin dir name relative to shop root'),
                    new InputOption('zipfile', null, InputOption::VALUE_OPTIONAL, 'Absolute path to zip file'),
                    new InputOption('delete', null, null, 'Delete zip and plugin dir after validating?'),
                ])
            );
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $pluginDir = \trim($input->getOption('plugin-dir') ?? '');
        while ($pluginDir === null || \strlen($pluginDir) < 3) {
            $pluginDir = $this->getIO()->ask('Plugin dir');
        }
        $input->setOption('plugin-dir', $pluginDir);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io         = $this->getIO();
        $pluginDir  = $input->getOption('plugin-dir');
        $delete     = $input->getOption('delete');
        $zip        = $input->getOption('zipfile');
        $parser     = new XMLParser();
        $pluginPath = \PFAD_ROOT . \PLUGIN_DIR . $pluginDir;
        if ($zip !== null) {
            if (!\file_exists($zip)) {
                $io->writeln("<error>Zipfile does not exist:</error> <comment>'{$zip}'</comment>");

                return -1;
            }
            $response = $this->unzip($zip, $parser);
            if ($response->getStatus() === InstallationResponse::STATUS_OK) {
                $io->writeln("<info>Successfully unzipped to</info> <comment>'{$response->getPath()}'</comment>");
            }
            if (!\is_dir($pluginPath) || \strpos($response->getDirName(), $pluginDir) === false) {
                $io->writeln('<error>Could not extract or wrong dir name</error>');
                $this->cleanup($delete, $pluginDir, $zip);

                return InstallCode::DIR_DOES_NOT_EXIST;
            }
        }
        if (\is_dir($pluginPath)) {
            $io->writeln("<info>Validating plugin at</info> <comment>'{$pluginDir}'</comment>");
            $validator = new PluginValidator(Shop::Container()->getDB(), $parser);
            $res       = $validator->validateByPath($pluginPath);
            if ($res === InstallCode::OK) {
                $io->writeln("<info>Successfully validated</info> <comment>'{$pluginDir}'</comment>");
            } else {
                $io->writeln('<error>Could not validate. Result code: ' . $res . '</error>');
            }
            $this->cleanup($delete, $pluginDir, $zip);

            return $res;
        }
        if (\is_dir(\PFAD_ROOT . \PFAD_PLUGIN . $pluginDir)) {
            $io->writeln("<info>Cannot validate legacy plugin at</info> <comment>'{$pluginDir}'</comment>");
        } else {
            $io->writeln("<error>No plugin dir:</error> <comment>'{$pluginDir}'</comment>");
        }
        $this->cleanup($delete, $pluginDir, $zip);

        return null;
    }

    /**
     * @param bool        $delete
     * @param string      $pluginPath
     * @param string|null $zip
     */
    private function cleanup(bool $delete, string $pluginPath, ?string $zip): void
    {
        if ($delete === true) {
            if (\file_exists($zip)) {
                \unlink($zip);
            }
            if (\is_dir($pluginPath)) {
                \rmdir($pluginPath);
            }
        }
    }

    /**
     * @param string    $zipfile
     * @param XMLParser $parser
     * @return InstallationResponse
     */
    private function unzip(string $zipfile, XMLParser $parser): InstallationResponse
    {
        $extractor = new Extractor($parser);

        return $extractor->extractPlugin($zipfile);
    }
}
