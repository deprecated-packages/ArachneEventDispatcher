<?php

namespace Arachne\EventDispatcher\DI;

use Arachne\EventDispatcher\ApplicationEvents;
use Arachne\EventDispatcher\Event\ApplicationErrorEvent;
use Arachne\EventDispatcher\Event\ApplicationEvent;
use Arachne\EventDispatcher\Event\ApplicationPresenterEvent;
use Arachne\EventDispatcher\Event\ApplicationRequestEvent;
use Arachne\EventDispatcher\Event\ApplicationResponseEvent;
use Arachne\EventDispatcher\Event\ApplicationShutdownEvent;
use Kdyby\Events\DI\EventsExtension;
use Nette\Application\Application;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\Utils\AssertionException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class EventDispatcherExtension extends CompilerExtension
{
    /**
     * Subscribers with this tag are added to the event dispatcher.
     */
    const TAG_SUBSCRIBER = 'arachne.eventDispatcher.subscriber';

    public function loadConfiguration()
    {
        $this->removeKdybyEventsSymfonyProxy();

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('eventDispatcher'))
            ->setClass(EventDispatcher::class);
    }

    public function beforeCompile()
    {
        $this->removeKdybyEventsSymfonyProxy();

        $builder = $this->getContainerBuilder();

        // Process event subscribers.
        $dispatcher = $builder->getDefinition($this->prefix('eventDispatcher'));
        foreach ($builder->findByTag(self::TAG_SUBSCRIBER) as $name => $attributes) {
            $class = $builder->getDefinition($name)->getClass();

            if (!is_subclass_of($class, EventSubscriberInterface::class)) {
                throw new AssertionException(
                    sprintf(
                        'Subscriber "%s" doesn\'t implement "%s".',
                        $name,
                        EventSubscriberInterface::class
                    )
                );
            }

            $this->registerSubscriber($dispatcher, $name, $class);
        }

        // Bind dispatcher to Nette\Application\Application events.
        foreach ($builder->findByType(Application::class) as $application) {
            $application->addSetup('$dispatcher = ?', ['@'.$this->prefix('eventDispatcher')]);
            $this->bindApplicationEvent($application, ApplicationEvents::STARTUP, ApplicationEvent::class, 'onStartup');
            $this->bindApplicationEvent($application, ApplicationEvents::SHUTDOWN, ApplicationShutdownEvent::class, 'onShutdown', 'exception');
            $this->bindApplicationEvent($application, ApplicationEvents::REQUEST, ApplicationRequestEvent::class, 'onRequest', 'request');
            $this->bindApplicationEvent($application, ApplicationEvents::PRESENTER, ApplicationPresenterEvent::class, 'onPresenter', 'presenter');
            $this->bindApplicationEvent($application, ApplicationEvents::RESPONSE, ApplicationResponseEvent::class, 'onResponse', 'response');
            $this->bindApplicationEvent($application, ApplicationEvents::ERROR, ApplicationErrorEvent::class, 'onError', 'exception');
        }

        // Bind dispatcher to console.
        foreach ($builder->findByType(ConsoleApplication::class) as $console) {
            $console->addSetup('setDispatcher');
        }
    }

    private function removeKdybyEventsSymfonyProxy()
    {
        $builder = $this->getContainerBuilder();

        // Remove Kdyby\Events\SymfonyDispatcher service to avoid conflict.
        foreach ($this->compiler->getExtensions(EventsExtension::class) as $eventsExtension) {
            $builder->removeDefinition($eventsExtension->prefix('symfonyProxy'));
        }
    }

    /**
     * @param ServiceDefinition $dispatcher
     * @param string            $service
     * @param string            $class
     */
    private function registerSubscriber(ServiceDefinition $dispatcher, $service, $class)
    {
        foreach ($class::getSubscribedEvents() as $event => $listeners) {
            if (is_string($listeners)) {
                $listeners = [[$listeners]];
            } elseif (is_string($listeners[0])) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listener) {
                $priority = isset($listener[1]) ? $listener[1] : 0;
                $method = $listener[0];

                $dispatcher->addSetup(
                    '?->addListener(?, function (...$arguments) { $this->getService(?)->?(...$arguments); }, ?)',
                    [
                        '@self',
                        $event,
                        $service,
                        $method,
                        $priority,
                    ]
                );
            }
        }
    }

    /**
     * Binds dispatcher to Nette\Application\Application event.
     *
     * @param ServiceDefinition $application
     * @param string            $event
     * @param string            $class
     * @param string            $property
     * @param string            $argument
     */
    private function bindApplicationEvent(ServiceDefinition $application, $event, $class, $property, $argument = null)
    {
        $argument = $argument ? ', $'.$argument : '';
        $application->addSetup(
            sprintf(
                '?->?[] = function ($application%s = null) use ($dispatcher) { $dispatcher->dispatch(?, new %s($application%s)); }',
                $argument,
                $class,
                $argument
            ),
            [
                '@self',
                $property,
                $event,
            ]
        );
    }
}
