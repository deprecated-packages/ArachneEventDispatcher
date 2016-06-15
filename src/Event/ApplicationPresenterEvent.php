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
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationPresenterEvent extends Event
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var IPresenter|Presenter
     */
    private $presenter;

    public function __construct(Application $application, IPresenter $presenter)
    {
        $this->application = $application;
        $this->presenter = $presenter;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return IPresenter|Presenter
     */
    public function getPresenter()
    {
        return $this->presenter;
    }
}
