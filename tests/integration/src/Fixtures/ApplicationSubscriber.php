<?php

namespace Tests\Integration\Fixtures;

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

    public static function getSubscribedEvents()
    {
        return [
            \Arachne\EventDispatcher\ApplicationEvents::STARTUP => 'event',
            \Arachne\EventDispatcher\ApplicationEvents::SHUTDOWN => 'event',
            \Arachne\EventDispatcher\ApplicationEvents::REQUEST => 'event',
            \Arachne\EventDispatcher\ApplicationEvents::PRESENTER => 'event',
            \Arachne\EventDispatcher\ApplicationEvents::RESPONSE => 'event',
            \Arachne\EventDispatcher\ApplicationEvents::ERROR => 'event',
        ];
    }

    public function setAssertionCallback(callable $assert)
    {
        $this->assert = $assert;
    }

    public function event()
    {
        call_user_func_array($this->assert, func_get_args());
    }
}
