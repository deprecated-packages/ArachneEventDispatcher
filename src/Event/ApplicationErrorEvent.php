<?php

namespace Arachne\EventDispatcher\Event;

use Nette\Application\Application;
use Symfony\Component\EventDispatcher\Event;
use Throwable;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationErrorEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Throwable
     */
    private $exception;

    public function __construct(Application $application, Throwable $exception)
    {
        $this->application = $application;
        $this->exception = $exception;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }
}
