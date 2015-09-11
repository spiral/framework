<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Reactor\Generators;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Service;
use Spiral\Http\Exceptions\MiddlewareException;
use Spiral\Http\MiddlewareInterface;
use Spiral\Reactor\Generators\Prototypes\AbstractService;

/**
 * Middleware generator.
 */
class MiddlewareGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->setUses([
            Service::class,
            MiddlewareInterface::class,
            ServerRequestInterface::class,
            ResponseInterface::class,
            MiddlewareException::class
        ]);

        $this->class->setExtends('Service');
        $this->class->addInterface('MiddlewareInterface');

        $invoke = $this->class->method(
            '__invoke',
            [
                "@param ServerRequestInterface \$request",
                "@param ResponseInterface      \$response",
                "@param \\Closure               \$next Next middleware/target. Always returns ResponseInterface.",
                "@return ResponseInterface"
            ],
            ['request', 'next']
        );

        $invoke->parameter('request')->setType('ServerRequestInterface');
        $invoke->parameter('response')->setType('ResponseInterface');
        $invoke->parameter('next')->setType('\Closure');

        $invoke->setSource([
            "return \$next(\$request, \$response);",
        ]);
    }
}