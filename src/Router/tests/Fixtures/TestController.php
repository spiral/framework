<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

use Spiral\Core\Exception\ControllerException;
use Nyholm\Psr7\Response;
use Spiral\Core\Internal\Introspector;

class TestController
{
    public function index(): string
    {
        return 'hello world';
    }

    public function test(): string
    {
        return 'hello world';
    }

    public function id(string $id): string
    {
        return $id;
    }

    public function default(string $id = 'default'): string
    {
        return $id;
    }

    public function defaultInt(string|int $id = 1): string
    {
        $result = \is_int($id) ? 'int: ' : 'string: ';

        return $result . $id;
    }

    public function echo(): void
    {
        ob_start();
        echo 'echoed';
    }

    public function err(): void
    {
        throw new \Error('error.controller');
    }

    public function rsp(): Response
    {
        $r = new Response();
        $r->getBody()->write('rsp');

        echo 'buf';

        return $r;
    }

    public function json(): array
    {
        return [
            'status' => 301,
            'msg'    => 'redirect',
        ];
    }

    public function forbidden(): void
    {
        throw new ControllerException('', ControllerException::FORBIDDEN);
    }

    public function notFound(): void
    {
        throw new ControllerException('', ControllerException::NOT_FOUND);
    }

    public function weird(): void
    {
        throw new ControllerException('', 99);
    }

    public function postTarget(): string
    {
        return 'POST';
    }

    public function deleteTarget(): string
    {
        return 'DELETE';
    }

    public function scopes(): string
    {
        $scopes = Introspector::scopeNames();
        return \implode(', ', $scopes);
    }
}
