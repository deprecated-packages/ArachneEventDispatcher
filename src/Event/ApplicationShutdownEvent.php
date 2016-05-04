<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EventDispatcher\Event;

use Exception;
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
     * @var Throwable|Exception|null
     */
    private $exception;

    public function __construct(Application $application, $exception = null)
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
     * @return Throwable|Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }
}
