<?php

declare(strict_types=1);

namespace Arachne\EventDispatcher\Event;

use Nette\Application\Application;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}
