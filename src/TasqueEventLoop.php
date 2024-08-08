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

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use InvalidArgumentException;
use LogicException;
use Nytris\Core\Package\PackageContextInterface;
use Nytris\Core\Package\PackageInterface;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;
use Tasque\EventLoop\Library\Library;
use Tasque\EventLoop\Library\LibraryInterface;
use Tasque\Tasque;

/**
 * Class TasqueEventLoop.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TasqueEventLoop implements TasqueEventLoopInterface
{
    private static bool $bootstrapped = false;
    private static ?LibraryInterface $library = null;

    /**
     * @inheritDoc
     */
    public static function await(PromiseInterface $promise): mixed
    {
        return self::getLibrary()->await($promise);
    }

    /**
     * Bootstrapping only ever happens once, either via Composer's file-autoload mechanism
     * or via TasqueEventLoop::install(...), whichever happens first.
     */
    public static function bootstrap(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        self::$bootstrapped = true;

        // Don't tock-ify the ReactPHP event loop logic itself for efficiency.
        Tasque::excludeComposerPackage('react/event-loop');

        // Exclude Tasque EventLoop itself from having tock hooks applied.
        Tasque::excludeFiles(new FileFilter(__DIR__ . '/**'));
    }

    /**
     * @inheritDoc
     */
    public static function getLibrary(): LibraryInterface
    {
        if (!self::$library) {
            throw new LogicException(
                'Library is not installed - did you forget to install this package in nytris.config.php?'
            );
        }

        return self::$library;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'event-loop';
    }

    /**
     * @inheritDoc
     */
    public static function getVendor(): string
    {
        return 'tasque';
    }

    /**
     * @inheritDoc
     */
    public static function getEventLoopThread(): ExternalControlInterface
    {
        return self::getLibrary()->getEventLoopThread();
    }

    /**
     * @inheritDoc
     */
    public static function install(PackageContextInterface $packageContext, PackageInterface $package): void
    {
        if (!$package instanceof TasqueEventLoopPackageInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Package config must be a %s but it was a %s',
                    TasqueEventLoopPackageInterface::class,
                    $package::class
                )
            );
        }

        self::bootstrap();

        $tasque = new Tasque();

        // Run the ReactPHP event loop itself inside a Tasque green thread.
        self::$library = new Library($tasque->createThread(function () {
            Loop::run();
        }));
    }

    /**
     * @inheritDoc
     */
    public static function isInstalled(): bool
    {
        return self::$library !== null;
    }

    /**
     * @inheritDoc
     */
    public static function setLibrary(LibraryInterface $library): void
    {
        if (self::$library !== null) {
            self::$library->uninstall();
        }

        self::$library = $library;
    }

    /**
     * @inheritDoc
     */
    public static function uninstall(): void
    {
        if (self::$library === null) {
            // Not yet installed anyway; nothing to do.
            return;
        }

        self::$library->uninstall();
        self::$library = null;
    }
}
