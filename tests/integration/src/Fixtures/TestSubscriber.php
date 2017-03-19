<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TestSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    public $log;

    public static function getSubscribedEvents(): array
    {
        return [
            'tests.event1' => 'event1',
            'tests.event2' => ['event2'],
            'tests.event3' => ['event3', 10],
            'tests.event4' => [
                ['event4handler1'],
                ['event4handler2', 10],
            ],
        ];
    }

    public function event1(): void
    {
        $this->log[] = __METHOD__;
    }

    public function event2(): void
    {
        $this->log[] = __METHOD__;
    }

    public function event3(): void
    {
        $this->log[] = __METHOD__;
    }

    public function event4handler1(): void
    {
        $this->log[] = __METHOD__;
    }

    public function event4handler2(): void
    {
        $this->log[] = __METHOD__;
    }
}
