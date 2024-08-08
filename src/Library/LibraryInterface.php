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

use React\Promise\PromiseInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;

/**
 * Interface LibraryInterface.
 *
 * Encapsulates an installation of the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface LibraryInterface
{
    /**
     * Awaits the given ReactPHP promise within the current thread,
     * which may also be the main thread.
     *
     * If it is fulfilled then the result will be returned as normal.
     * If it is rejected then the throwable will be thrown as normal.
     *
     * @template T
     * @param PromiseInterface<T> $promise
     * @return T
     */
    public function await(PromiseInterface $promise): mixed;

    /**
     * Fetches the Tasque thread that is running the ReactPHP event loop.
     */
    public function getEventLoopThread(): ExternalControlInterface;

    /**
     * Determines whether the library is still installed.
     */
    public function isInstalled(): bool;

    /**
     * Uninstalls this installation of the library.
     */
    public function uninstall(): void;
}
