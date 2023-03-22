<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware declaration.
 */
class MiddlewareDeclaration extends AbstractDeclaration
{
    public const TYPE = 'middleware';

    /**
     * Declare default process method body.
     */
    public function declare(): void
    {
        $this->class->addImplement(MiddlewareInterface::class);

        $this->class
            ->addMethod('process')
            ->setPublic()
            ->setReturnType(ResponseInterface::class)
            ->addBody('return $handler->handle($request);');

        $this->class->getMethod('process')
            ->addParameter('request')
            ->setType(ServerRequestInterface::class);

        $this->class->getMethod('process')
            ->addParameter('handler')
            ->setType(RequestHandlerInterface::class);
    }
}
