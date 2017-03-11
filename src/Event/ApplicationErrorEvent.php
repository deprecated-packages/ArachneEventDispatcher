<?php

namespace Arachne\EventDispatcher\Event;

use Exception;
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
     * @var Throwable|Exception
     */
    private $exception;

    public function __construct(Application $application, $exception)
    {
        $this->application = $application;
        $this->exception = $exception;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return Throwable|Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
