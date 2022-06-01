<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

use Spiral\Core\Exception\ControllerException;
use Nyholm\Psr7\Response;

class TestController
{
    public function index()
    {
        return 'hello world';
    }

    public function test()
    {
        return 'hello world';
    }

    public function id(string $id)
    {
        return $id;
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

    public function rsp()
    {
        $r = new Response();
        $r->getBody()->write('rsp');

        echo 'buf';

        return $r;
    }

    public function json()
    {
        return [
            'status' => 301,
            'msg'    => 'redirect'
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

    public function postTarget()
    {
        return 'POST';
    }

    public function deleteTarget()
    {
        return 'DELETE';
    }
}
