<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EventDispatcher\Event;

use Nette\Application\Application;
use Nette\Application\Request;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationRequestEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Application $application, Request $request)
    {
        $this->application = $application;
        $this->request = $request;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
