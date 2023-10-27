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
 * Class Log.
 *
 * Used by tests to record a log in a structured way that can be injected.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Log
{
    /**
     * @var string[]
     */
    private array $log = [];

    /**
     * @return string[]
     */
    public function getLog(): array
    {
        return $this->log;
    }

    public function log(string $message): void
    {
        $this->log[] = $message;
    }
}
