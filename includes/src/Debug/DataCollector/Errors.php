<?php declare(strict_types=1);

namespace JTL\Debug\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use stdClass;

/**
 * Class Errors
 * @package JTL\Debug\DataCollector
 */
class Errors extends DataCollector implements Renderable
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * Errors constructor.
     */
    public function __construct()
    {
        \set_error_handler([$this, 'handleError']);
    }

    /**
     * @param  int    $level
     * @param  string $message
     * @param  string $file
     * @param  int    $line
     * @param  array  $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = []): void
    {
        $error          = new stdClass();
        $error->level   = $level;
        $error->message = $message;
        $error->file    = $file;
        $error->line    = $line;
        $error->context = $context;
        $this->errors[] = $error;
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $data = [];
        foreach ($this->errors as $idx => $var) {
            $data[\basename($var->file) . ':' . $var->line] = $this->getDataFormatter()->formatVar($var);
        }

        return ['errors' => $data, 'count' => \count($data)];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'errors';
    }

    /**
     * @return array
     */
    public function getWidgets(): array
    {
        $name = $this->getName();
        return [
            $name            => [
                'icon'    => 'tags',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => $name . '.errors',
                'default' => '{}'
            ],
            $name . ':badge' => [
                'map'     => $name . '.count',
                'default' => 'null'
            ]
        ];
    }
}
