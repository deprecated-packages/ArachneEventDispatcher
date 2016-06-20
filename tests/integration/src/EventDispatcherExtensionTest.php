<?php

namespace Tests\Integration;

use Arachne\EventDispatcher\ApplicationEvents;
use Arachne\EventDispatcher\Event\ApplicationErrorEvent;
use Arachne\EventDispatcher\Event\ApplicationEvent;
use Arachne\EventDispatcher\Event\ApplicationPresenterEvent;
use Arachne\EventDispatcher\Event\ApplicationRequestEvent;
use Arachne\EventDispatcher\Event\ApplicationResponseEvent;
use Arachne\EventDispatcher\Event\ApplicationShutdownEvent;
use Codeception\Test\Unit;
use Exception;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\Integration\Fixtures\ApplicationSubscriber;
use Tests\Integration\Fixtures\TestSubscriber;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EventDispatcherExtensionTest extends Unit
{
    /**
     * @expectedException Nette\Utils\AssertionException
     * @expectedExceptionMessage Subscriber 'subscriber' doesn't implement 'Symfony\Component\EventDispatcher\EventSubscriberInterface'.
     */
    public function testSubscriberException()
    {
        $this->tester->useConfigFiles(['config/subscriber-exception.neon']);
        $this->tester->getContainer();
    }

    public function testSubscriber()
    {
        $this->tester->useConfigFiles(['config/subscriber.neon']);

        /* @var $dispatcher ContainerAwareEventDispatcher */
        $dispatcher = $this->tester->grabService(EventDispatcherInterface::class);
        $subscriber = $this->tester->grabService(TestSubscriber::class);

        $dispatcher->dispatch('tests.event1');
        $this->assertSame(0, $dispatcher->getListenerPriority('tests.event1', [$subscriber, 'event1']));
        $dispatcher->dispatch('tests.event2');
        $this->assertSame(0, $dispatcher->getListenerPriority('tests.event2', [$subscriber, 'event2']));
        $dispatcher->dispatch('tests.event3');
        $this->assertSame(10, $dispatcher->getListenerPriority('tests.event3', [$subscriber, 'event3']));
        $dispatcher->dispatch('tests.event4');
        $this->assertSame(0, $dispatcher->getListenerPriority('tests.event4', [$subscriber, 'event4handler1']));
        $this->assertSame(10, $dispatcher->getListenerPriority('tests.event4', [$subscriber, 'event4handler2']));

        $this->assertSame(
            [
                TestSubscriber::class.'::event1',
                TestSubscriber::class.'::event2',
                TestSubscriber::class.'::event3',
                TestSubscriber::class.'::event4handler2',
                TestSubscriber::class.'::event4handler1',
            ],
            $subscriber->log
        );
    }

    public function testApplicationEvents()
    {
        $this->tester->useConfigFiles(['config/application.neon']);

        /* @var $application Application */
        $application = $this->tester->grabService(Application::class);
        /* @var $subscriber ApplicationSubscriber */
        $subscriber = $this->tester->grabService(ApplicationSubscriber::class);

        $called = [];

        $subscriber->setAssertionCallback(function ($event, $name) use (&$called) {
            $called[] = $name;

            switch ($name) {
                case ApplicationEvents::STARTUP:
                    $this->assertInstanceOf(ApplicationEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    break;

                case ApplicationEvents::SHUTDOWN:
                    $this->assertInstanceOf(ApplicationShutdownEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertInstanceOf(Exception::class, $event->getException());
                    break;

                case ApplicationEvents::REQUEST:
                    $this->assertInstanceOf(ApplicationRequestEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertInstanceOf(Request::class, $event->getRequest());
                    break;

                case ApplicationEvents::PRESENTER:
                    $this->assertInstanceOf(ApplicationPresenterEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertInstanceOf(IPresenter::class, $event->getPresenter());
                    break;

                case ApplicationEvents::RESPONSE:
                    $this->assertInstanceOf(ApplicationResponseEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertInstanceOf(IResponse::class, $event->getResponse());
                    break;

                case ApplicationEvents::ERROR:
                    $this->assertInstanceOf(ApplicationErrorEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertInstanceOf(Exception::class, $event->getException());
                    break;

                default:
                    $this->fail("Unknown event '$name'");
            }
        });

        $application->run();

        $this->assertSame(
            [
                ApplicationEvents::STARTUP,
                ApplicationEvents::ERROR,
                ApplicationEvents::REQUEST,
                ApplicationEvents::PRESENTER,
                ApplicationEvents::RESPONSE,
                ApplicationEvents::SHUTDOWN,
            ],
            $called
        );
    }

    public function testApplicationShutdownWithoutException()
    {
        $this->tester->useConfigFiles(['config/application.neon']);

        /* @var $application Application */
        $application = $this->tester->grabService(Application::class);
        /* @var $subscriber ApplicationSubscriber */
        $subscriber = $this->tester->grabService(ApplicationSubscriber::class);

        $called = [];

        $subscriber->setAssertionCallback(function ($event, $name) use (&$called) {
            $called[] = $name;

            switch ($name) {
                case ApplicationEvents::SHUTDOWN:
                    $this->assertInstanceOf(ApplicationShutdownEvent::class, $event);
                    $this->assertInstanceOf(Application::class, $event->getApplication());
                    $this->assertNull($event->getException());
                    break;

                default:
                    $this->fail("Unknown event '$name'");
            }
        });

        $application->onShutdown($application);

        $this->assertSame(
            [
                ApplicationEvents::SHUTDOWN,
            ],
            $called
        );
    }

    public function testConsole()
    {
        $this->tester->useConfigFiles(['config/console.neon']);
        $application = $this->tester->grabService(Console::class);
        $this->assertInstanceOf(Console::class, $application);
        $this->assertAttributeSame($this->tester->grabService(EventDispatcherInterface::class), 'dispatcher', $application);
    }
}
