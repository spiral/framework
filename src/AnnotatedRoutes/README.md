Annotated Routes
========
[![Latest Stable Version](https://poser.pugx.org/spiral/annotated-routes/v/stable)](https://packagist.org/packages/spiral/annotated-routes) 
[![Build Status](https://github.com/spiral/annotated-routes/workflows/build/badge.svg)](https://github.com/spiral/annotated-routes/actions)
[![Codecov](https://codecov.io/gh/spiral/annotated-routes/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/annotated-routes/)

Provides the ability to use annotated routes and route groups.

Installation:
--------
To install the component:

```bash
$ composer require spiral/annotated-routes
```

Activate the bootloader in your application (instead of `RouteBootloader`):

```php
use Spiral\Router\Bootloader as Router;

// ...

protected const LOAD = [
    // ...
    Router\AnnotatedRoutesBootloader::class,
    // ...
];
```

Configuration:
-------
Define one or multiple route groups (optional) or configure `default` group in your bootloader:

```php
namespace App\Bootloader;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Router\GroupRegistry;

class MiddlewareBootloader extends Bootloader
{
    public function boot(GroupRegistry $registry)
    {
        $registry
            ->getGroup('api')
            ->setPrefix('api/v1')
            ->addMiddleware(App\Security\ApiAuthMiddleware::class);
    }
}
```

Use environment variable `ROUTE_CACHE` to enable or disable route caching (by default enabled in non `DEBUG` mode). To 
reset route cache:

```bash
$ php app.php route:reset
```

Usage:
--------
Use the annotation in your controller methods:

```php
namespace App\Controller;

use Spiral\Router\Annotation\Route;

class HomeController
{
    /**
     * @Route(route="/", name="home", methods="GET", group="api")
     */
    public function index()
    {
        return 'Hello World';
    }
}
```

> Use `group` attribute to configure middleware and domain core in bulk.

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
