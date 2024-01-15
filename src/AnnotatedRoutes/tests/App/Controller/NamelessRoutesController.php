<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\App\Controller;

use Spiral\Router\Annotation\Route;

class NamelessRoutesController
{
    #[Route('/nameless', methods: 'GET')]
    public function index(): string
    {
        return 'index';
    }

    #[Route(route: '/nameless', methods: 'POST')]
    public function method(): string
    {
        return 'method';
    }

    #[Route(route: '/nameless/route', methods: ['GET', 'POST'])]
    public function route(): string
    {
        return 'route';
    }
}
