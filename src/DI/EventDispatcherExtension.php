<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EventDispatcher\DI;

use Arachne\EventDispatcher\ApplicationEvents;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\Utils\AssertionException;

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
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('eventDispatcher'))
            ->setClass('Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher');
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        // Process event subscribers.
        $dispatcher = $builder->getDefinition($this->prefix('eventDispatcher'));
        foreach ($builder->findByTag(self::TAG_SUBSCRIBER) as $name => $attributes) {
            $class = $builder->getDefinition($name)->getClass();

            if (!is_subclass_of($class, 'Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
                throw new AssertionException("Subscriber '$name' doesn't implement 'Symfony\Component\EventDispatcher\EventSubscriberInterface'.");
            }

            $this->registerSubscriber($dispatcher, $name, $class);
        }

        // Bind dispatcher to Nette\Application\Application events.
        foreach ($builder->findByType('Nette\Application\Application') as $application) {
            $application->addSetup('$dispatcher = ?', ['@' . $this->prefix('eventDispatcher')]);
            $this->bindApplicationEvent($application, ApplicationEvents::STARTUP, 'Arachne\EventDispatcher\Event\ApplicationEvent', 'onStartup');
            $this->bindApplicationEvent($application, ApplicationEvents::SHUTDOWN, 'Arachne\EventDispatcher\Event\ApplicationShutdownEvent', 'onShutdown', 'exception');
            $this->bindApplicationEvent($application, ApplicationEvents::REQUEST, 'Arachne\EventDispatcher\Event\ApplicationRequestEvent', 'onRequest', 'request');
            $this->bindApplicationEvent($application, ApplicationEvents::PRESENTER, 'Arachne\EventDispatcher\Event\ApplicationPresenterEvent', 'onPresenter', 'presenter');
            $this->bindApplicationEvent($application, ApplicationEvents::RESPONSE, 'Arachne\EventDispatcher\Event\ApplicationResponseEvent', 'onResponse', 'response');
            $this->bindApplicationEvent($application, ApplicationEvents::ERROR, 'Arachne\EventDispatcher\Event\ApplicationErrorEvent', 'onError', 'exception');
        }

        // Bind dispatcher to console.
        foreach ($builder->findByType('Symfony\Component\Console\Application') as $console) {
            $console->addSetup('setDispatcher');
        }
    }

    /**
     * Emulates ContainerAwareEventDispatcher::addSubscriberService() to prevent autoloading of all subscribers in runtime.
     *
     * @param ServiceDefinition $dispatcher
     * @param string $service
     * @param string $class
     */
    private function registerSubscriber(ServiceDefinition $dispatcher, $service, $class)
    {
        foreach ($class::getSubscribedEvents() as $event => $params) {
            if (is_string($params)) {
                $dispatcher->addSetup('?->addListenerService(?, ?)', [
                    '@self',
                    $event,
                    [$service, $params], //callback
                ]);
            } elseif (is_string($params[0])) {
                $dispatcher->addSetup('?->addListenerService(?, ?, ?)', [
                    '@self',
                    $event,
                    [$service, $params[0]], //callback
                    isset($params[1]) ? $params[1] : 0, // priority
                ]);
            } else {
                foreach ($params as $listener) {
                    $dispatcher->addSetup('?->addListenerService(?, ?, ?)', [
                        '@self',
                        $event,
                        [$service, $listener[0]], //callback
                        isset($listener[1]) ? $listener[1] : 0, // priority
                    ]);
                }
            }
        }
    }

    /**
     * Binds dispatcher to Nette\Application\Application event.
     *
     * @param ServiceDefinition $application
     * @param string $event
     * @param string $class
     * @param string $property
     * @param string $argument
     */
    private function bindApplicationEvent(ServiceDefinition $application, $event, $class, $property, $argument = null)
    {
        $argument = $argument ? ', $' . $argument : '';
        $application->addSetup('?->?[] = function ($application' . $argument . ' = null) use ($dispatcher) { $dispatcher->dispatch(?, new ' . $class . '($application' . $argument . ')); }', [
            '@self',
            $property,
            $event,
        ]);
    }
}
