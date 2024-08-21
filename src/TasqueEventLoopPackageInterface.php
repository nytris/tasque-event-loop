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

use Nytris\Core\Package\PackageInterface;
use React\EventLoop\LoopInterface;
use Tasque\EventLoop\ContextSwitch\SchedulerInterface;

/**
 * Interface TasqueEventLoopPackageInterface.
 *
 * Configures the installation of Tasque EventLoop.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface TasqueEventLoopPackageInterface extends PackageInterface
{
    public const DEFAULT_CONTEXT_SWITCH_INTERVAL = 0.0005;

    /**
     * Fetches the interval in seconds to context switch away from the event loop
     * to process other green threads.
     */
    public function getContextSwitchInterval(): float;

    /**
     * Fetches the context switch scheduler.
     */
    public function getContextSwitchScheduler(): SchedulerInterface;

    /**
     * Fetches the ReactPHP event loop to use, or null to use the default.
     */
    public function getEventLoop(): ?LoopInterface;
}
