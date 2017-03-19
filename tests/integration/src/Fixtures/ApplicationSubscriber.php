<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Arachne\EventDispatcher\ApplicationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationSubscriber implements EventSubscriberInterface
{
    /**
     * @var callable
     */
    private $assert;

    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::STARTUP => 'event',
            ApplicationEvents::SHUTDOWN => 'event',
            ApplicationEvents::REQUEST => 'event',
            ApplicationEvents::PRESENTER => 'event',
            ApplicationEvents::RESPONSE => 'event',
            ApplicationEvents::ERROR => 'event',
        ];
    }

    public function setAssertionCallback(callable $assert): void
    {
        $this->assert = $assert;
    }

    public function event(...$arguments): void
    {
        ($this->assert)(...$arguments);
    }
}
