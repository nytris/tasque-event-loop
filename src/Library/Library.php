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

namespace Tasque\EventLoop\Library;

use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;
use Tasque\Tasque;
use Throwable;

/**
 * Class Library.
 *
 * Encapsulates an installation of the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Library implements LibraryInterface
{
    private bool $installed = true;

    public function __construct(
        private readonly ?ExternalControlInterface $eventLoopThread
    ) {
        /*
         * Install a ReactPHP EventLoop future tick handler that switches the Tasque context.
         *
         * This ensures that the event loop is interrupted at least every tick to process context switches,
         * preventing the event loop from blocking other Tasque threads.
         * It also keeps the event loop alive indefinitely (unless it is explicitly stopped
         * or this package is uninstalled).
         */

        $tickTock = function () use (&$tickTock) {
            if ($this->installed === false) {
                return;
            }

            /*
             * Invoke the Tasque scheduler to handle other green threads as applicable.
             *
             * Note that we do not call `Marshaller::tock()`, as depending on the scheduler strategy in use,
             * a context switch may not happen for a while, which will waste resources
             * if the event loop has no work to perform.
             */
            Tasque::switchContext();

            Loop::futureTick($tickTock);
        };

        Loop::futureTick($tickTock);

        // Propagate any Throwables from the event loop up to the main thread.
        $this->eventLoopThread->shout();

        $this->eventLoopThread->start();
    }

    /**
     * @inheritDoc
     */
    public function await(PromiseInterface $promise): mixed
    {
        $done = false;
        /** @var Throwable|null $exception */
        $exception = null;
        /** @var mixed $result */
        $result = null;

        $promise
            ->then(function (mixed $fulfilment) use (&$done, &$result) {
                $done = true;
                $result = $fulfilment;
            }, function (Throwable $rejection) use (&$done, &$exception) {
                $done = true;
                $exception = $rejection;
            });

        while (!$done) {
            Tasque::switchContext();

            // TODO: Pause, perhaps only if there are no other free threads to switch to,
            //       to prevent busy-waiting.
        }

        if ($exception !== null) {
            throw $exception;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getEventLoopThread(): ExternalControlInterface
    {
        return $this->eventLoopThread;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        if (!$this->installed) {
            return;
        }

        $this->eventLoopThread->terminate();

        $this->installed = false;
    }
}
