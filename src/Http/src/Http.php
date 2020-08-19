<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Exception\HttpException;

final class Http implements RequestHandlerInterface
{
    /** @var HttpConfig */
    protected $config;

    /** @var Pipeline */
    protected $pipeline;

    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var ContainerInterface */
    protected $container;

    /** @var RequestHandlerInterface */
    protected $handler;

    /**
     * @param HttpConfig               $config
     * @param Pipeline                 $pipeline
     * @param ResponseFactoryInterface $responseFactory
     * @param ContainerInterface       $container
     */
    public function __construct(
        HttpConfig $config,
        Pipeline $pipeline,
        ResponseFactoryInterface $responseFactory,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->pipeline = $pipeline;
        $this->responseFactory = $responseFactory;
        $this->container = $container;

        foreach ($this->config->getMiddleware() as $middleware) {
            $this->pipeline->pushMiddleware($this->container->get($middleware));
        }
    }

    /**
     * @return Pipeline
     */
    public function getPipeline(): Pipeline
    {
        return $this->pipeline;
    }

    /**
     * @param RequestHandlerInterface|callable $handler
     * @return Http
     */
    public function setHandler($handler): self
    {
        if ($handler instanceof RequestHandlerInterface) {
            $this->handler = $handler;
        } elseif (is_callable($handler)) {
            $this->handler = new CallableHandler($handler, $this->responseFactory);
        } else {
            throw new HttpException(
                'Invalid handler is given, expects callable or RequestHandlerInterface.'
            );
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->handler)) {
            throw new HttpException('Unable to run HttpCore, no handler is set.');
        }

        return $this->pipeline->withHandler($this->handler)->handle($request);
    }
}
