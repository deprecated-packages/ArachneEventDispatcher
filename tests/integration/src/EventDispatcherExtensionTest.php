<?php

declare(strict_types=1);

namespace Tests\Integration;

use Arachne\Codeception\Module\NetteDIModule;
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
use Nette\Utils\AssertionException;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\Integration\Fixtures\ApplicationSubscriber;
use Tests\Integration\Fixtures\TestSubscriber;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EventDispatcherExtensionTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    public function testSubscriberException(): void
    {
        $this->tester->useConfigFiles(['config/subscriber-exception.neon']);
        try {
            $this->tester->getContainer();
            $this->fail();
        } catch (AssertionException $e) {
            self::assertSame('Subscriber "subscriber" doesn\'t implement "Symfony\Component\EventDispatcher\EventSubscriberInterface".', $e->getMessage());
        }
    }

    public function testSubscriber(): void
    {
        $this->tester->useConfigFiles(['config/subscriber.neon']);

        /* @var $dispatcher ContainerAwareEventDispatcher */
        $dispatcher = $this->tester->grabService(EventDispatcherInterface::class);

        // Make sure the subscriber is initialized lazily.
        self::assertFalse($this->tester->getContainer()->isCreated('subscriber'));
        $subscriber = $this->tester->grabService(TestSubscriber::class);
        self::assertTrue($this->tester->getContainer()->isCreated('subscriber'));

        $dispatcher->dispatch('tests.event1');
        $dispatcher->dispatch('tests.event2');
        $dispatcher->dispatch('tests.event3');
        $dispatcher->dispatch('tests.event4');

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

    public function testApplicationEvents(): void
    {
        $this->tester->useConfigFiles(['config/application.neon']);

        /** @var Application $application */
        $application = $this->tester->grabService(Application::class);
        /** @var ApplicationSubscriber $subscriber */
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

    public function testApplicationShutdownWithoutException(): void
    {
        $this->tester->useConfigFiles(['config/application.neon']);

        /** @var Application $application */
        $application = $this->tester->grabService(Application::class);
        /** @var ApplicationSubscriber $subscriber */
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

    public function testKdybyEvents()
    {
        $this->tester->useConfigFiles(['config/kdyby-events.neon']);
        $this->assertInstanceOf(EventDispatcher::class, $this->tester->grabService(EventDispatcherInterface::class));
    }
}
