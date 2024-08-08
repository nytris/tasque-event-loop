# Tasque EventLoop

[![Build Status](https://github.com/nytris/tasque-event-loop/workflows/CI/badge.svg)](https://github.com/nytris/tasque-event-loop/actions?query=workflow%3ACI)

Run a [ReactPHP][2] [event loop][3] within a conventional PHP application using [Tasque][1].

## Why?
To allow periodic background tasks, such as sending keep-alive or heartbeat messages,
to be performed in a traditional PHP environment where there would otherwise be no event loop.

## Usage
Install this package with Composer:

```shell
$ composer install tasque/event-loop
```

Configure Nytris platform:

`nytris.config.php`

```php
<?php

declare(strict_types=1);

use Nytris\Boot\BootConfig;
use Nytris\Boot\PlatformConfig;
use Tasque\EventLoop\TasqueEventLoopPackage;
use Tasque\TasquePackage;

$bootConfig = new BootConfig(new PlatformConfig(__DIR__ . '/var/cache/nytris/'));

$bootConfig->installPackage(new TasquePackage());
$bootConfig->installPackage(new TasqueEventLoopPackage());

return $bootConfig;
```

### Setting a timer

Just use a standard ReactPHP timer, as long as the package is configured in `nytris.config.php` as above.

`index.php`

```php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;

require_once __DIR__ . '/vendor/autoload.php';

Loop::addPeriodicTimer(1, function () {
    print 'Timer elapsed' . PHP_EOL;
});
```

### Awaiting a ReactPHP promise from a thread

```php
<?php

declare(strict_types=1);

use Tasque\EventLoop\TasqueEventLoop;

/*
 * This will cause the current thread (which may be the main thread
 * as well as a background thread) to pause until the promise is resolved,
 * in which case the result will be returned, or rejected, in which case
 * the reason will be thrown.
 */
$result = TasqueEventLoop::await($promise);
```

## See also
- [Tasque][1]
- [ReactPHP][2]
- [ReactPHP EventLoop][3]

[1]: https://github.com/nytris/tasque
[2]: https://reactphp.org/
[3]: https://github.com/reactphp/event-loop
