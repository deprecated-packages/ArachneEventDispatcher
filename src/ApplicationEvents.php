<?php

namespace Arachne\EventDispatcher;

/**
 * Events in Nette\Application\Application life cycle.
 *
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationEvents
{
    /**
     * The STARTUP event occurs before the application loads presenter.
     *
     * @see Nette\Application\Application::$onStartup
     * @Event("Arachne\EventDispatcher\Event\ApplicationEvent")
     */
    const STARTUP = 'nette.application.startup';

    /**
     * The SHUTDOWN event occurs before the application shuts down.
     *
     * @see Nette\Application\Application::$onShutdown
     * @Event("Arachne\EventDispatcher\Event\ApplicationShutdownEvent")
     */
    const SHUTDOWN = 'nette.application.shutdown';

    /**
     * The REQUEST event occurs when a new request is received.
     *
     * @see Nette\Application\Application::$onRequest
     * @Event("Arachne\EventDispatcher\Event\ApplicationRequestEvent")
     */
    const REQUEST = 'nette.application.request';

    /**
     * The PRESENTER event when a presenter is created.
     *
     * @see Nette\Application\Application::$onPresenter
     * @Event("Arachne\EventDispatcher\Event\ApplicationPresenterEvent")
     */
    const PRESENTER = 'nette.application.presenter';

    /**
     * The RESPONSE event occurs when a new response is ready for dispatch.
     *
     * @see Nette\Application\Application::$onResponse
     * @Event("Arachne\EventDispatcher\Event\ApplicationResponseEvent")
     */
    const RESPONSE = 'nette.application.response';

    /**
     * The ERROR event when an unhandled exception occurs in the application.
     *
     * @see Nette\Application\Application::$onError
     * @Event("Arachne\EventDispatcher\Event\ApplicationErrorEvent")
     */
    const ERROR = 'nette.application.error';
}
