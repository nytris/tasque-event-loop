<?php

/*
 * Tasque EventLoop - ReactPHP event loop using a Tasque green thread.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/nytris/tasque-event-loop/
 *
 * Released under the MIT license.
 * https://github.com/nytris/tasque-event-loop/raw/main/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Tasque\EventLoop;

use Nytris\Core\Package\PackageFacadeInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;

/**
 * Interface TasqueEventLoopInterface.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface TasqueEventLoopInterface extends PackageFacadeInterface
{
    /**
     * Fetches the Tasque thread that is running the ReactPHP event loop.
     */
    public static function getEventLoopThread(): ExternalControlInterface;
}
