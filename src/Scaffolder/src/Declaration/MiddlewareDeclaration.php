<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

/**
 * Middleware declaration.
 */
class MiddlewareDeclaration extends ClassDeclaration implements DependedInterface
{
    /**
     * @param string $name
     * @param string $comment
     */
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, '', ['MiddlewareInterface'], $comment);

        $this->declareStructure();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            MiddlewareInterface::class     => null,
            RequestHandlerInterface::class => null,
            ResponseInterface::class       => 'Response',
            ServerRequestInterface::class  => 'Request'
        ];
    }

    /**
     * Declare default process method body.
     */
    private function declareStructure(): void
    {
        $method = $this->method('process')->setPublic();

        $method->setComment('{@inheritdoc}');
        $method->parameter('request')->setType('Request');
        $method->parameter('handler')->setType('RequestHandlerInterface');

        $method->setReturn('Response');

        $method->setSource('return $handler->handle($request);');
    }
}
