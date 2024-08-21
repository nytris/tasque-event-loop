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

namespace Tasque\EventLoop\ContextSwitch;

use React\EventLoop\LoopInterface;

/**
 * Class TimerScheduler.
 *
 * Handles periodically context switching away from the event loop to process other green threads.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TimerScheduler implements SchedulerInterface
{
    /**
     * @inheritDoc
     */
    public function schedule(
        callable $tickTock,
        LoopInterface $eventLoop,
        float $contextSwitchInterval
    ): void {
        $eventLoop->addTimer($contextSwitchInterval, $tickTock);
    }
}
