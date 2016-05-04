<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EventDispatcher\Event;

use Nette\Application\Application;
use Nette\Application\IResponse;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationResponseEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var IResponse
     */
    private $response;

    public function __construct(Application $application, IResponse $response)
    {
        $this->application = $application;
        $this->response = $response;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return IResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}
