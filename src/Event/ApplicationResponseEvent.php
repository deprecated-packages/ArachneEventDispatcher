<?php

declare(strict_types=1);

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
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @return IResponse
     */
    public function getResponse(): IResponse
    {
        return $this->response;
    }
}
