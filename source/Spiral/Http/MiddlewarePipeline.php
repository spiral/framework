<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\Container\Autowire;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Http\Exceptions\MiddlewareException;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Http\Traits\MiddlewaresTrait;

/**
 * Pipeline used to pass request and response thought the chain of middlewares.
 *
 * Spiral middlewares are similar to Laravel's one. However router and http itself
 * can be in used in zend expressive.
 */
class MiddlewarePipeline
{
    use JsonTrait, MiddlewaresTrait;

    /**
     * @invisible
     * @var ContainerInterface
     */
    private $container;

    /**
     * Endpoint should be called at the deepest level of pipeline.
     *
     * @var callable
     */
    private $target = null;

    /**
     * @param callable[]|MiddlewareInterface[] $middlewares
     * @param ContainerInterface               $container Spiral container is needed, due scoping.
     *
     * @throws ScopeException
     */
    public function __construct(array $middlewares = [], ContainerInterface $container)
    {
        $this->middlewares = $middlewares;
        $this->container = $container;
    }

    /**
     * Set pipeline target.
     *
     * @param callable $target
     *
     * @return $this|self
     */
    public function target(callable $target): MiddlewarePipeline
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        return $this->run($request, $response);
    }

    /**
     * Pass request and response though every middleware to target and return generated and wrapped
     * response.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     *
     * @throws MiddlewareException
     */
    public function run(Request $request, Response $response): Response
    {
        if (empty($this->target)) {
            throw new MiddlewareException("Unable to run pipeline without specified target");
        }

        return $this->next(0, $request, $response);
    }

    /**
     * Get next chain to be called. Exceptions will be converted to responses.
     *
     * @param int      $position
     * @param Request  $request
     * @param Response $response
     *
     * @return null|Response
     * @throws \Exception
     */
    protected function next(int $position, Request $request, Response $response)
    {
        if (!isset($this->middlewares[$position])) {
            //Middleware target endpoint to be called and converted into response
            return $this->mountResponse($request, $response);
        }

        /**
         * @var callable $next
         */
        $next = $this->middlewares[$position];

        if (is_string($next) || $next instanceof Autowire) {
            //Resolve using container
            $next = $this->container->get($next);
        }

        //Executing next middleware
        return $next($request, $response, $this->getNext($position, $request, $response));
    }

    /**
     * Run pipeline target and return generated response.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     *
     * @throws \Throwable
     */
    protected function mountResponse(Request $request, Response $response): Response
    {
        $outputLevel = ob_get_level();
        ob_start();

        $output = '';
        $result = null;

        $scope = [
            $this->container->replace(Request::class, $request),
            $this->container->replace(Response::class, $response)
        ];

        try {
            $result = call_user_func($this->target, $request, $response);
        } catch (\Throwable $e) {
            //Close buffer due error
            ob_get_clean();
            throw  $e;
        } finally {
            foreach (array_reverse($scope) as $payload) {
                $this->container->restore($payload);
            }

            while (ob_get_level() > $outputLevel + 1) {
                $output = ob_get_clean() . $output;
            }
        }

        return $this->wrapResponse($response, $result, ob_get_clean() . $output);
    }

    /**
     * Convert endpoint result into valid response.
     *
     * @param Response $response Initial pipeline response.
     * @param mixed    $result   Generated endpoint output.
     * @param string   $output   Buffer output.
     *
     * @return Response
     */
    private function wrapResponse(Response $response, $result = null, string $output = ''): Response
    {
        if ($result instanceof Response) {
            if (!empty($output) && $result->getBody()->isWritable()) {
                $result->getBody()->write($output);
            }

            return $result;
        }

        if (is_array($result) || $result instanceof \JsonSerializable) {
            $response = $this->writeJson($response, $result);
        } else {
            $response->getBody()->write($result);
        }

        //Always glue buffered output
        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Get next callable element.
     *
     * @param int      $position
     * @param Request  $outerRequest
     * @param Response $outerResponse
     *
     * @return \Closure
     */
    private function getNext(
        int $position,
        Request $outerRequest,
        Response $outerResponse
    ): \Closure {
        $next = function ($request = null, $response = null) use (
            $position,
            $outerRequest,
            $outerResponse
        ) {
            //This function will be provided to next (deeper) middleware
            return $this->next(
                ++$position,
                $request ?? $outerRequest,
                $response ?? $outerResponse
            );
        };

        return $next;
    }
}
