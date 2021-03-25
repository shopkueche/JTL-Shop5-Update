<?php

namespace JTL\Events;

use JTL\SingletonTrait;
use stdClass;
use function Functional\pluck;

/**
 * Class Dispatcher
 * @package JTL\Events
 */
final class Dispatcher
{
    use SingletonTrait;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    private $listeners = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    private $wildcards = [];

    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) || isset($this->wildcards[$eventName]);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array $eventNames
     * @param callable     $listener
     * @param int          $priority
     */
    public function listen($eventNames, callable $listener, int $priority = 5): void
    {
        foreach ((array)$eventNames as $event) {
            $item = (object)['listener' => $listener, 'priority' => $priority];
            if (\mb_strpos($event, '*') !== false) {
                $this->wildcards[$event][] = $item;
            } else {
                $this->listeners[$event][] = $item;
            }
        }
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param string|object $eventName
     * @param array|object $arguments
     */
    public function fire(string $eventName, $arguments = []): void
    {
        foreach ($this->getListeners($eventName) as $listener) {
            $listener($arguments);
        }
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $eventName
     * @return void
     */
    public function forget(string $eventName): void
    {
        if (\mb_strpos($eventName, '*') !== false) {
            if (isset($this->wildcards[$eventName])) {
                unset($this->wildcards[$eventName]);
            }
        } elseif (isset($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param string $eventName
     * @return array
     */
    public function getListeners(string $eventName): array
    {
        $listeners = $this->getWildcardListeners($eventName);
        if (isset($this->listeners[$eventName])) {
            $listeners = \array_merge($listeners, $this->listeners[$eventName]);
        }
        \usort($listeners, [$this, 'sortByPriority']);

        return pluck($listeners, 'listener');
    }

    /**
     * @param stdClass $a
     * @param stdClass $b
     * @return int
     */
    private function sortByPriority(stdClass $a, stdClass $b): int
    {
        return $a->priority <=> $b->priority;
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param  string  $eventName
     * @return array
     */
    private function getWildcardListeners(string $eventName): array
    {
        $wildcards = [];
        foreach ($this->wildcards as $key => $listeners) {
            if (\fnmatch($key, $eventName)) {
                $wildcards = \array_merge($wildcards, $listeners);
            }
        }

        return $wildcards;
    }
}
