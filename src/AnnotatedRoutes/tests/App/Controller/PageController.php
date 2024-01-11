<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\App\Controller;

use Spiral\Router\Annotation\Route;

final class PageController
{
    #[Route('/page/<page>', name: 'page_get', methods: 'GET')]
    public function get($page): string
    {
        return 'page-' . $page;
    }

    #[Route('/page/about', name: 'page_about', methods: 'GET', priority: -1)]
    public function about(): string
    {
        return 'about';
    }
}
