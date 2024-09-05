<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\App\Request\BadRequest;
use Spiral\App\Request\TestRequest;
use Spiral\Filter\InputScope;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Pagination\Paginator;
use Spiral\Router\RouteInterface;
use Spiral\Translator\Traits\TranslatorTrait;

class TestController
{
    use TranslatorTrait;

    public function index(string $name = 'Dave')
    {
        return "Hello, {$name}.";
    }

    public function paginate(PaginationProviderInterface $paginationFactory): int
    {
        /** @var Paginator $p */
        $p = $paginationFactory->createPaginator('page');

        return $p->withCount(1000)->getPage();
    }

    public function filter(TestRequest $r)
    {
        return ['name' => $r->name, 'sectionValue' => $r->sectionValue];
    }

    public function filter2(BadRequest $r): void
    {
    }

    public function input(InputScope $i)
    {
        return 'value: ' . $i->withPrefix('section')->getValue('query', 'value');
    }

    public function error(): void
    {
        throw new \InvalidArgumentException('Invalid argument');
    }

    public function route(RouteInterface $route)
    {
        return $route->getMatches();
    }

    public function payload(ServerRequestInterface $request)
    {
        return $request->getBody();
    }

    public function required(int $id)
    {
        //no index
        $this->say(static::class);
        $this->say('Hello world');
        $this->say('Hello world', [], 'external');

        l('l');
        l('l', [], 'external');
        p('%s unit|%s units', 10);
        p('%s unit|%s units', 10, [], 'external');

        return $id;
    }
}
