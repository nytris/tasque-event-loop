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

use React\EventLoop\LoopInterface;
use Tasque\EventLoop\ContextSwitch\SchedulerInterface;
use Tasque\EventLoop\ContextSwitch\TimerScheduler;

/**
 * Class TasqueEventLoopPackage.
 *
 * Configures the installation of Tasque EventLoop.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TasqueEventLoopPackage implements TasqueEventLoopPackageInterface
{
    public function __construct(
        /**
         * ReactPHP event loop to use. Specify null to use the default (recommended).
         */
        private readonly ?LoopInterface $eventLoop = null,
        /**
         * Interval in seconds to context switch away from the ReactPHP event loop
         * to process other green threads.
         */
        private readonly float $contextSwitchInterval = self::DEFAULT_CONTEXT_SWITCH_INTERVAL,
        /**
         * Scheduler to use to handle periodically context switching away from the ReactPHP event loop
         * to process other green threads.
         */
        private readonly SchedulerInterface $contextSwitchScheduler = new TimerScheduler()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getContextSwitchInterval(): float
    {
        return $this->contextSwitchInterval;
    }

    /**
     * @inheritDoc
     */
    public function getContextSwitchScheduler(): SchedulerInterface
    {
        return $this->contextSwitchScheduler;
    }

    /**
     * @inheritDoc
     */
    public function getEventLoop(): ?LoopInterface
    {
        return $this->eventLoop;
    }

    /**
     * @inheritDoc
     */
    public function getPackageFacadeFqcn(): string
    {
        return TasqueEventLoop::class;
    }
}
