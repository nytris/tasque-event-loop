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

namespace Tasque\EventLoop\Tests\Functional\TasqueEventLoop\Await;

use Nytris\Core\Package\PackageContextInterface;
use Tasque\Core\Scheduler\ContextSwitch\NTockStrategy;
use Tasque\EventLoop\ContextSwitch\FutureTickScheduler;
use Tasque\EventLoop\TasqueEventLoop;
use Tasque\EventLoop\TasqueEventLoopPackageInterface;
use Tasque\EventLoop\Tests\AbstractTestCase;
use Tasque\EventLoop\Tests\Functional\Harness\Log;
use Tasque\EventLoop\Tests\Functional\Harness\TasqueEventLoop\Await\MainThread;
use Tasque\Tasque;
use Tasque\TasquePackageInterface;

/**
 * Class WithOtherBackgroundThreadTest.
 *
 * Tests the TasqueEventLoop::await(...) method with another background thread
 * in addition to the ReactPHP event loop thread and main thread.
 *
 * - The main thread
 * - The Tasque EventLoop thread
 * - One other background thread.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class WithOtherBackgroundThreadTest extends AbstractTestCase
{
    private Log $log;
    private Tasque $tasque;

    public function setUp(): void
    {
        Tasque::install(mock(PackageContextInterface::class), mock(TasquePackageInterface::class, [
            'getSchedulerStrategy' => new NTockStrategy(1),
            'isPreemptive' => true,
        ]));
        TasqueEventLoop::install(
            mock(PackageContextInterface::class),
            mock(TasqueEventLoopPackageInterface::class, [
                'getContextSwitchInterval' => TasqueEventLoopPackageInterface::DEFAULT_CONTEXT_SWITCH_INTERVAL,
                // Switch every tick to make tests deterministic.
                'getContextSwitchScheduler' => new FutureTickScheduler(),
                'getEventLoop' => null,
            ])
        );

        $this->log = new Log();
        $this->tasque = new Tasque();
    }

    public function tearDown(): void
    {
        TasqueEventLoop::uninstall();
        Tasque::uninstall();
    }

    public function testThreadsAreScheduledCorrectly(): void
    {
        (new MainThread($this->tasque, $this->log))->run();

        static::assertEquals(
            [
                'Start of main thread run',
                'Before adding read stream',
                'After adding read stream',
                'Before background thread start',
                'After background thread start',

                'Before promise await',
                'Start of background thread #1 run',
                'Background thread #1 loop iteration #0',
                'Background thread #1 loop iteration #1',
                'Resolving promise',
                'Background thread #1 loop iteration #2',
                'Background thread #1 loop iteration #3',
                'End of background thread #1 run',
                'After promise await',

                'Before writing to stream',
                'After writing to stream',
                'Before main thread loop',
                'Main thread loop iteration #0',
                'Main thread loop iteration #1',
                'Read stream received: "My data"',
                'Main thread loop iteration #2',
                'Main thread loop iteration #3',
                'After main thread loop',
                'Before join',
                'After join',
                'End of main thread run',
            ],
            $this->log->getLog()
        );
    }
}
