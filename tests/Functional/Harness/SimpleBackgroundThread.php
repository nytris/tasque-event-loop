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

namespace Tasque\EventLoop\Tests\Functional\Harness;

/**
 * Class SimpleBackgroundThread.
 *
 * Used by NTockStrategy tests.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SimpleBackgroundThread
{
    public function __construct(
        private readonly Log $log,
        private readonly int $threadId
    ) {
    }

    public function run(): void
    {
        $this->log->log('Start of background thread #' . $this->threadId . ' run');

        for ($i = 0; $i < 4; $i++) {
            $this->log->log('Background thread #' . $this->threadId . ' loop iteration #' . $i);
        }

        $this->log->log('End of background thread #' . $this->threadId . ' run');
    }
}
