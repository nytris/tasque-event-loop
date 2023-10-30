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

/**
 * Class TasqueEventLoopPackage.
 *
 * Configures the installation of Tasque EventLoop.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TasqueEventLoopPackage implements TasqueEventLoopPackageInterface
{
    /**
     * @inheritDoc
     */
    public function getPackageFacadeFqcn(): string
    {
        return TasqueEventLoop::class;
    }
}
