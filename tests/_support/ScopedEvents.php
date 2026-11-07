<?php

use yii\base\Event;

/**
 * Class-level event handlers that are removed after each test.
 *
 * Event::offAll() would also drop the handlers the plugin and Craft register at bootstrap,
 * making later tests depend on run order.
 */
trait ScopedEvents
{
    /**
     * @var array<array{string, string, callable}>
     */
    private array $scopedHandlers = [];

    protected function onEvent(string $class, string $name, callable $handler): void
    {
        Event::on($class, $name, $handler);
        $this->scopedHandlers[] = [$class, $name, $handler];
    }

    protected function offScopedEvents(): void
    {
        foreach ($this->scopedHandlers as [$class, $name, $handler]) {
            Event::off($class, $name, $handler);
        }
        $this->scopedHandlers = [];
    }
}
