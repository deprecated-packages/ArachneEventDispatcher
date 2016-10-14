Documentation
====

This package integrates [symfony/event-dispatcher](https://github.com/symfony/event-dispatcher) to Nette Framework.

Installation
----

The best way to install Arachne/EventDispatcher is using [Composer](http://getcomposer.org/).

```sh
$ composer require arachne/event-dispatcher
```

Now add these extensions to your config.neon.

```yml
extensions:
    arachne.containeradapter: Arachne\ContainerAdapter\DI\ContainerAdapterExtension
    arachne.eventdispatcher: Arachne\EventDispatcher\DI\EventDispatcherExtension
```

See the documentation of [symfony/event-dispatcher](http://symfony.com/doc/current/components/event_dispatcher/index.html) for details.

Troubleshooting
----

```
Nette\DI\ServiceCreationException:
Multiple services of type Symfony\Component\EventDispatcher\EventDispatcherInterface found:
arachne.eventdispatcher.eventDispatcher, kdyby.events.symfonyProxy
```

This exception means you've installed arachne/event-dispatcher, kdyby/events and some other library (like kdyby/console) in a wrong order in your config.neon. Moving `Arachne\EventDispatcher\DI\EventDispatcherExtension` directly after `Kdyby\Events\DI\EventsExtension` should solve this issue.

Subscribers
----

To add register an event subscriber, add it to your config.neon.

```
services:
    subscriber:
        class: App\AdminModule\Event\Subscriber
        tags:
            - arachne.eventDispatcher.subscriber
```

You can also simplify it using the DecoratorExtension from Nette.

```
decorator:
    Symfony\Component\EventDispatcher\EventSubscriberInterface:
        tags:
            - arachne.eventDispatcher.subscriber

services:
    subscriber: App\AdminModule\Event\Subscriber
```

Application events
----

If you're using [nette/application](https://github.com/nette/application) the events in `Nette/Application/Application` will be fired by the event dispatcher as well. See the [ApplicationEvents](../src/ApplicationEvents.php) class for details.
