<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\App\Controller;

use Spiral\Router\Annotation\Route;

class HomeController
{
    /**
     * @Route(route="/", name="index", methods="GET")
     */
    public function index(): string
    {
        return 'index';
    }

    /**
     * @Route(route="/", name="method", methods="POST")
     */
    public function method(): string
    {
        return 'method';
    }

    #[Route(route: '/attribute', name: 'attribute', methods: 'GET', group: 'test')]
    public function attribute(): string
    {
        return 'attribute';
    }
}
