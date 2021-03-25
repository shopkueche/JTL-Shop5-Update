<?php declare(strict_types=1);

namespace JTL\Console\Command;

use JTL\Console\Application;
use JTL\Console\ConsoleIO;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Command
 * @package JTL\Console\Command
 */
class Command extends BaseCommand
{
    /**
     * Command constructor.
     *
     * @param string|null $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return Application|\Symfony\Component\Console\Application
     */
    public function getApp()
    {
        return $this->getApplication();
    }

    /**
     * @return ConsoleIO
     */
    public function getIO(): ConsoleIO
    {
        return $this->getApp()->getIO();
    }

    /**
     * @param string $name
     * @return InputArgument
     */
    public function getArgumentDefinition($name): InputArgument
    {
        return $this->getDefinition()->getArgument($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMissingOption($name): bool
    {
        $option = $this->getDefinition()->getOption($name);
        $value  = \trim($this->getIO()->getInput()->getOption($name) ?? '');

        return $option->isValueRequired() && $option->acceptValue() && empty($value);
    }

    /**
     * @param string $name
     * @return InputOption
     */
    public function getOptionDefinition($name): InputOption
    {
        return $this->getDefinition()->getOption($name);
    }

    /**
     * @param string $name
     * @return string|array
     */
    public function getOption($name)
    {
        $value = $this->getIO()->getInput()->getOption($name);

        return \is_string($value) ? \trim($value) : $value;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->getIO()->getInput()->getOptions();
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasOption($name): bool
    {
        return $this->getIO()->getInput()->hasOption($name);
    }
}
