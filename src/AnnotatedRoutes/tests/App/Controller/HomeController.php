<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router\App\Controller;

use Spiral\Router\Annotation\Route;

class HomeController
{
    /**
     * @Route(route="/", name="index", methods="GET")
     */
    public function index()
    {
        return 'index';
    }

    /**
     * @Route(route="/", name="method", methods="POST")
     */
    public function method()
    {
        return 'method';
    }
}
