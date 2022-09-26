<?php

declare(strict_types=1);

namespace Spiral\Filter;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\ErrorsRendererInterface;

final class ValidationHandlerMiddleware implements MiddlewareInterface
{
    protected ErrorsRendererInterface $renderErrors;

    /**
     * @param ErrorsRendererInterface|null $renderErrors Renderer for all filter errors.
     *        By default, will be used {@see JsonErrorsRenderer}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ContainerInterface $container,
        ?ErrorsRendererInterface $renderErrors = null
    ) {
        $this->renderErrors = $renderErrors ?? $container->get(JsonErrorsRenderer::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            return $this->renderErrors->render($e->errors, $e->context);
        }
    }
}
