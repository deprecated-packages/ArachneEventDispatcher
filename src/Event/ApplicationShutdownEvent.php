<?php

declare(strict_types=1);

namespace Arachne\EventDispatcher\Event;

use Nette\Application\Application;
use Symfony\Component\EventDispatcher\Event;
use Throwable;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationShutdownEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Throwable|null
     */
    private $exception;

    public function __construct(Application $application, ?Throwable $exception = null)
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
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }
}
