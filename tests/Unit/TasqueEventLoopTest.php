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

namespace Tasque\EventLoop\Tests\Unit;

use LogicException;
use Mockery\MockInterface;
use Nytris\Core\Package\PackageContextInterface;
use Nytris\Core\Package\PackageInterface;
use React\Promise\PromiseInterface;
use Tasque\Core\Thread\Control\ExternalControlInterface;
use Tasque\EventLoop\Library\LibraryInterface;
use Tasque\EventLoop\TasqueEventLoop;
use Tasque\EventLoop\TasqueEventLoopPackageInterface;
use Tasque\EventLoop\Tests\AbstractTestCase;
use Tasque\Tasque;
use Tasque\TasquePackageInterface;

/**
 * Class TasqueEventLoopTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TasqueEventLoopTest extends AbstractTestCase
{
    private MockInterface&PackageContextInterface $eventLoopPackageContext;
    private MockInterface&PackageInterface $eventLoopPackage;
    private MockInterface&PackageContextInterface $tasquePackageContext;
    private MockInterface&TasquePackageInterface $tasquePackage;

    public function setUp(): void
    {
        $this->eventLoopPackage = mock(TasqueEventLoopPackageInterface::class);
        $this->eventLoopPackageContext = mock(PackageContextInterface::class);
        $this->tasquePackage = mock(TasquePackageInterface::class, [
            'getSchedulerStrategy' => null,
        ]);
        $this->tasquePackageContext = mock(PackageContextInterface::class);

        TasqueEventLoop::uninstall();
    }

    public function tearDown(): void
    {
        TasqueEventLoop::uninstall();
    }

    public function testPackageIsDefinedCorrectly(): void
    {
        static::assertSame('tasque', TasqueEventLoop::getVendor());
        static::assertSame('event-loop', TasqueEventLoop::getName());
    }

    public function testAwaitRaisesExceptionWhenPackageNotLoaded(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Library is not installed - did you forget to install this package in nytris.config.php?'
        );

        TasqueEventLoop::await(mock(PromiseInterface::class));
    }

    public function testAwaitInvokesAwaitOnTheLibrary(): void
    {
        $library = mock(LibraryInterface::class, [
            'uninstall' => null
        ]);
        TasqueEventLoop::setLibrary($library);
        $promise = mock(PromiseInterface::class);

        $library->expects()
            ->await($promise)
            ->once();

        TasqueEventLoop::await($promise);
    }

    public function testGetEventLoopThreadRaisesExceptionWhenPackageNotLoaded(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Library is not installed - did you forget to install this package in nytris.config.php?'
        );

        TasqueEventLoop::getEventLoopThread();
    }

    public function testInstallCreatesTasqueEventLoopThread(): void
    {
        Tasque::install($this->tasquePackageContext, $this->tasquePackage);
        TasqueEventLoop::install($this->eventLoopPackageContext, $this->eventLoopPackage);

        static::assertInstanceOf(ExternalControlInterface::class, TasqueEventLoop::getEventLoopThread());
    }

    public function testInstallMarksTasqueEventLoopThreadAsShouting(): void
    {
        Tasque::install($this->tasquePackageContext, $this->tasquePackage);
        TasqueEventLoop::install($this->eventLoopPackageContext, $this->eventLoopPackage);

        static::assertTrue(TasqueEventLoop::getEventLoopThread()->isShouting());
    }

    public function testUninstallUninstallsTasqueEventLoop(): void
    {
        Tasque::install($this->tasquePackageContext, $this->tasquePackage);
        TasqueEventLoop::install($this->eventLoopPackageContext, $this->eventLoopPackage);

        TasqueEventLoop::uninstall();

        static::assertFalse(TasqueEventLoop::isInstalled());
    }
}
