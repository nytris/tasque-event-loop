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
use React\Promise\PromiseInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;
use Tasque\EventLoop\Library\LibraryInterface;

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
    public static function await(PromiseInterface $promise): mixed;

    /**
     * Fetches the Tasque thread that is running the ReactPHP event loop.
     */
    public static function getEventLoopThread(): ExternalControlInterface;

    /**
     * Fetches the current library installation.
     */
    public static function getLibrary(): LibraryInterface;

    /**
     * Overrides the current library installation with the given one.
     */
    public static function setLibrary(LibraryInterface $library): void;
}
