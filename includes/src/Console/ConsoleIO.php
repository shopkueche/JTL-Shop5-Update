<?php declare(strict_types=1);

namespace JTL\Console;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Terminal;

/**
 * Class ConsoleIO
 * @package JTL\Console
 */
class ConsoleIO extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;

    /**
     * @var int
     */
    protected $lastMessagesLength = 0;

    /**
     * @var bool
     */
    protected $overwrite = true;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var
     */
    private $questionHelper;

    /**
     * @var
     */
    private $progressBar;

    /**
     * @var int
     */
    private $lineLength;

    /**
     * @var BufferedOutput
     */
    private $bufferedOutput;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * ConsoleIO constructor.
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param HelperSet|null  $helperSet
     */
    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet = null)
    {
        $formatter = null;
        if ($output->getFormatter() !== null) {
            $formatter = clone $output->getFormatter();
        }

        $this->input          = $input;
        $this->output         = $output;
        $this->helperSet      = $helperSet;
        $this->bufferedOutput = new BufferedOutput($output->getVerbosity(), false, $formatter);
        $this->lineLength     = $this->getTerminalWidth() - (int)(\DIRECTORY_SEPARATOR === '\\');

        parent::__construct($output);
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return HelperSet|null
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * @inheritDoc
     */
    public function isQuiet()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_QUIET;
    }

    /**
     * @return bool
     */
    public function isNormal()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL;
    }

    /**
     * @inheritDoc
     */
    public function isVerbose()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * @inheritDoc
     */
    public function isVeryVerbose()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * @inheritDoc
     */
    public function isDebug()
    {
        return $this->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * @param $message
     * @return $this
     */
    public function overwrite($message)
    {
        $lines = \explode("\n", $message);
        if ($this->lastMessagesLength !== null) {
            foreach ($lines as $i => $line) {
                $len = Helper::strlenWithoutDecoration($this->bufferedOutput->getFormatter(), $line);
                if ($this->lastMessagesLength > $len) {
                    $lines[$i] = $line . \str_repeat("\x20", $this->lastMessagesLength - $len);
                }
            }
        }
        if ($this->overwrite) {
            $this->write("\x0D");
        }

        $this->lastMessagesLength = 0;
        foreach ($lines as $line) {
            $len = Helper::strlenWithoutDecoration($this->bufferedOutput->getFormatter(), $line);
            if ($len > $this->lineLength) {
                $line = \substr($line, 0, $this->lineLength);
            }

            $this->write($line);

            if ($len > $this->lastMessagesLength) {
                $this->lastMessagesLength = $len;
            }
        }

        return $this;
    }

    /**
     * @param      $process
     * @param null $format
     * @param bool $clearMessage
     * @return $this
     */
    public function progress($process, $format = null, $clearMessage = true)
    {
        $progress = parent::createProgressBar();

        if ($format === null) {
            $format = '%percent:3s%% [%bar%] %current% of %max%';
        }

        $progress->setFormat($format);
        $progress->setMessage('');
        $progress->setEmptyBarCharacter(' ');
        $progress->setBarCharacter('<comment>=</comment>');
        // $progress->setRedrawFrequency(1);

        $lastPercent = 0;
        $lastMessage = null;
        $lastRedraw  = \microtime(true);

        $callback = static function (
            $total,
            $current,
            $message = ''
        ) use (
            &$progress,
            &$lastRedraw,
            &$lastPercent,
            &$lastMessage
        ) {
            if ($progress->getMaxSteps() === 0) {
                $progress->start($total);
            }

            // update frequence 250ms or on percent value changed
            $off = (\microtime(true) - $lastRedraw) * 1000;
            if ($off > 250 || $lastPercent !== $current || $lastMessage !== $message) {
                $progress->setMessage($message);
                if ($current > $lastPercent) {
                    $progress->setProgress($current);
                }
                $progress->display();
                $lastRedraw  = \microtime(true);
                $lastPercent = $current;
                $lastMessage = $message;
            }
        };

        if (\is_callable($process)) {
            $process($callback);
        }

        if ($clearMessage) {
            $progress->setMessage('');
        }
        $progress->finish();
        $this->writeln('');

        return $this;
    }

    /**
     * @param $current
     * @param $limit
     * @param $step
     * @return $this
     */
    public function setStep($current, $limit, $step)
    {
        $this->setLabel('Step ' . $current . ' of ' . $limit, $step);

        return $this;
    }

    /**
     * @param      $title
     * @param null $sub
     * @return $this
     */
    public function setLabel($title, $sub = null)
    {
        $this->writeln('');
        $this->writeln('<comment>' . $title . '</comment> ' . ($sub !== null ? '<info>' . $sub . '</info>' : ''));
        $this->writeln('');

        return $this;
    }

    /**
     * @return bool
     */
    public function isInteractive()
    {
        return $this->getInput()->hasOption('no-interaction') === false;
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     * @param string|null  $type The block type (added in [] on first line)
     * @param string|null  $style The style to apply to the whole block
     * @param string       $prefix The prefix for the block
     * @param bool         $padding Whether to add vertical padding
     *
     * @return $this
     */
    public function block($messages, $type = null, $style = null, $prefix = ' ', $padding = false)
    {
        $this->autoPrependBlock();

        $messages = \is_array($messages) ? \array_values($messages) : [$messages];
        $lines    = [];
        // add type
        if ($type !== null) {
            $messages[0] = \sprintf('[%s] %s', $type, $messages[0]);
        }

        // wrap and add newlines for each element
        foreach ($messages as $key => $message) {
            $message = OutputFormatter::escape($message);
            $lines   = \array_merge(
                $lines,
                \explode(
                    \PHP_EOL,
                    \wordwrap($message, $this->lineLength - Helper::strlen($prefix), \PHP_EOL, true)
                )
            );

            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }

        if ($padding && $this->isDecorated()) {
            \array_unshift($lines, '');
            $lines[] = '';
        }

        $length = \max(
            \array_map(
                function ($line) {
                    return Helper::strlenWithoutDecoration($this->getFormatter(), $line);
                },
                $lines
            )
        );

        $length += \strlen($prefix) * 2;

        foreach ($lines as &$line) {
            $line  = \sprintf('%s%s', $prefix, $line);
            $line .= \str_repeat(' ', $length - Helper::strlenWithoutDecoration($this->getFormatter(), $line));

            if ($style) {
                $line = \sprintf('<%s>%s</>', $style, $line);
            }
        }
        unset($line);

        $this->writeln($lines);
        $this->newLine();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function title($message)
    {
        $this->autoPrependBlock();

        $this->writeln(
            [
                \sprintf('<comment>%s</comment>', $message),
                \sprintf(
                    '<comment>%s</comment>',
                    \str_repeat('=', Helper::strlenWithoutDecoration($this->getFormatter(), $message))
                ),
            ]
        );
        $this->newLine();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function section($message)
    {
        $this->autoPrependBlock();

        $this->writeln(
            [
                \sprintf('<comment>%s</comment>', $message),
                \sprintf(
                    '<comment>%s</comment>',
                    \str_repeat('-', Helper::strlenWithoutDecoration($this->getFormatter(), $message))
                ),
            ]
        );
        $this->newLine();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function listing(array $elements)
    {
        $this->autoPrependText();

        $elements = \array_map(
            static function ($element) {
                return \sprintf(' * %s', $element);
            },
            $elements
        );

        $this->writeln($elements);
        $this->newLine();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
        $this->autoPrependText();

        $messages = \is_array($message) ? \array_values($message) : [$message];
        foreach ($messages as $msg) {
            $this->writeln(\sprintf(' %s', $msg));
        }

        return $this;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    public function comment($message)
    {
        $this->autoPrependText();

        $messages = \is_array($message) ? \array_values($message) : [$message];
        foreach ($messages as $msg) {
            $this->writeln(\sprintf('<fg=white;bg=magenta>%s</>', $msg));
        }

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function verbose($message)
    {
        return $this->block($message, null, 'fg=black;bg=cyan', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        return $this->block($message, null, 'fg=black;bg=green', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        return $this->block($message, null, 'fg=white;bg=red', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        return $this->block($message, null, 'fg=black;bg=yellow', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
        return $this->block($message, null, 'fg=white;bg=blue', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function caution($message)
    {
        return $this->block($message, null, 'fg=white;bg=red', ' ', true);
    }

    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows, array $options = [])
    {
        $options = \array_merge([
            'style' => 'symfony-style-guide'
        ], $options);
        $headers = \array_map(
            static function ($value) {
                return \sprintf('<info>%s</info>', $value);
            },
            $headers
        );

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($options['style']);

        if (isset($options['columnWidth']) && \count($options['columnWidth']) > 0) {
            $table->setColumnWidths($options['columnWidth']);
        }

        $table->render();
        $this->newLine();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null, $validator = null)
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function askHidden($question, $validator = null)
    {
        $question = new Question($question);

        $question->setHidden(true);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * {@inheritdoc}
     */
    public function confirm($question, $default = true)
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }

    /**
     * {@inheritdoc}
     */
    public function choice($question, array $choices, $default = null)
    {
        if ($default !== null) {
            $values  = \array_flip($choices);
            $default = $values[$default];
        }

        return $this->askQuestion(new ChoiceQuestion($question, $choices, $default));
    }

    /**
     * {@inheritdoc}
     */
    public function progressStart($max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function progressAdvance($step = 1)
    {
        $this->getProgressBar()->advance($step);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        $this->progressBar = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createProgressBar($max = 0)
    {
        $progressBar = parent::createProgressBar($max);

        if ('\\' !== \DIRECTORY_SEPARATOR) {
            $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        return $progressBar;
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    public function askQuestion(Question $question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        if (!$this->questionHelper) {
            $this->questionHelper = new SymfonyQuestionHelper();
        }

        $answer = $this->questionHelper->ask($this->input, $this, $question);

        if ($this->input->isInteractive()) {
            $this->newLine();
        }

        return $answer;
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        parent::writeln($messages, $type);
        $this->bufferedOutput->writeln($this->reduceBuffer($messages), $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        parent::write($messages, $newline, $type);
        $this->bufferedOutput->write($this->reduceBuffer($messages), $newline, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newLine($count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(\str_repeat("\n", $count));

        return $this;
    }

    /**
     * @return ProgressBar
     */
    private function getProgressBar()
    {
        if (!$this->progressBar) {
            throw new RuntimeException('The ProgressBar is not started.');
        }

        return $this->progressBar;
    }

    /**
     * @return int
     */
    private function getTerminalWidth()
    {
        $terminal   = new Terminal();
        $dimensions = [$terminal->getWidth(), $terminal->getHeight()];

        return $dimensions[0] ?: self::MAX_LINE_LENGTH;
    }

    /**
     * @return $this|ConsoleIO|void
     */
    private function autoPrependBlock()
    {
        $chars = \substr(\str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);

        if (!isset($chars[0])) {
            return $this->newLine(); //empty history, so we should start with a new line.
        }
        //Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - \substr_count($chars, "\n"));

        return $this;
    }

    /**
     * @return $this
     */
    private function autoPrependText()
    {
        $fetched = $this->bufferedOutput->fetch();

        //Prepend new line if last char isn't EOL:
        if (\substr($fetched, -1) !== "\n") {
            $this->newLine();
        }

        return $this;
    }

    /**
     * @param $messages
     * @return array
     */
    private function reduceBuffer($messages)
    {
        // We need to know if the two last chars are PHP_EOL
        // Preserve the last 4 chars inserted (PHP_EOL on windows is two chars) in the history buffer
        return \array_map(
            static function ($value) {
                return \substr($value, -4);
            },
            \array_merge([$this->bufferedOutput->fetch()], (array)$messages)
        );
    }
}
