<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Vladislav Gorenkin (vladgorenkin)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router\App\Controller;

use Spiral\Router\Annotation\Route;

class NamelessRoutesController
{
    /**
     * @Route(route="/nameless",  methods="GET")
     */
    public function index()
    {
        return 'index';
    }

    /**
     * @Route(route="/nameless", methods="POST")
     */
    public function method()
    {
        return 'method';
    }

    /**
     * @Route(route="/nameless/route", methods={"GET", "POST"})
     */
    public function route()
    {
        return 'route';
    }
}
