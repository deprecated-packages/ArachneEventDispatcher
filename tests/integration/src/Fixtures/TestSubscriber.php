<?php

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

    public static function getSubscribedEvents()
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

    public function event1()
    {
        $this->log[] = __METHOD__;
    }

    public function event2()
    {
        $this->log[] = __METHOD__;
    }

    public function event3()
    {
        $this->log[] = __METHOD__;
    }

    public function event4handler1()
    {
        $this->log[] = __METHOD__;
    }

    public function event4handler2()
    {
        $this->log[] = __METHOD__;
    }
}
