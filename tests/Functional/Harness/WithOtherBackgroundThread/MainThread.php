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

namespace Tasque\EventLoop\Tests\Functional\Harness\WithOtherBackgroundThread;

use React\EventLoop\Loop;
use Tasque\EventLoop\Tests\Functional\Harness\Log;
use Tasque\EventLoop\Tests\Functional\Harness\SimpleBackgroundThread;
use Tasque\TasqueInterface;

/**
 * Class MainThread.
 *
 * Used by NTockStrategy\SingleBackgroundThreadTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MainThread
{
    public function __construct(
        private readonly TasqueInterface $tasque,
        private readonly Log $log
    ) {
    }

    public function run(): void
    {
        $this->log->log('Start of main thread run');

        $this->log->log('Before adding read stream');
        $stream = tmpfile();
        Loop::addReadStream($stream, function ($readStream) {
            $data = fread($readStream, 1024);

            if ($data !== '') {
                $this->log->log('Read stream received: "' . $data . '"');
            }
        });
        $this->log->log('After adding read stream');

        $backgroundThread = $this->tasque->createThread(
            (new SimpleBackgroundThread($this->log, 1))->run(...)
        );

        $this->log->log('Before background thread start');
        $backgroundThread->start();
        $this->log->log('After background thread start');

        $this->log->log('Before writing to stream');
        fwrite($stream, 'My data');
        rewind($stream);
        $this->log->log('After writing to stream');

        $this->log->log('Before main thread loop');
        for ($i = 0; $i < 4; $i++) {
            $this->log->log('Main thread loop iteration #' . $i);
        }
        $this->log->log('After main thread loop');

        $this->log->log('Before join');
        $backgroundThread->join();
        $this->log->log('After join');

        $this->log->log('End of main thread run');
    }
}
