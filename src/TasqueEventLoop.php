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
use Composer\InstalledVersions;
use Nytris\Core\Package\PackageConfigInterface;
use React\EventLoop\Loop;
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
    private static bool $installed = false;

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
    public static function install(PackageConfigInterface $packageConfig): void
    {
        self::$installed = true;

        $tasque = new Tasque();

        $reactEventLoopInstallPath = realpath(InstalledVersions::getInstallPath('react/event-loop'));
        $tasque->excludeFiles(new FileFilter($reactEventLoopInstallPath . '/**'));

        /*
         * Install a ReactPHP EventLoop future tick handler that invokes the Tasque tock hook.
         *
         * This ensures that the event loop is interrupted at least every tick to process tocks,
         * preventing the event loop from blocking other Tasque threads.
         * It also keeps the event loop alive indefinitely (unless it is explicitly stopped
         * or this package is uninstalled).
         */

        $tickTock = static function () use (&$tickTock) {
            if (self::$installed === false) {
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

        // Run the ReactPHP event loop itself inside a Tasque green thread.
        $eventLoopThread = $tasque->createThread(function () {
            Loop::run();
        });

        $eventLoopThread->start();
    }

    /**
     * @inheritDoc
     */
    public static function uninstall(): void
    {
        self::$installed = false;
    }
}
