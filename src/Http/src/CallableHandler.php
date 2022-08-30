<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Traits\JsonTrait;

/**
 * Provides ability to invoke any handler and write it's response into ResponseInterface.
 */
final class CallableHandler implements RequestHandlerInterface
{
    use JsonTrait;

    /** @var callable */
    private $callable;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(callable $callable, ResponseFactoryInterface $responseFactory)
    {
        $this->callable = $callable;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritdoc
     * @psalm-suppress UnusedVariable
     */
    public function handle(Request $request): Response
    {
        $outputLevel = ob_get_level();
        ob_start();

        $output = $result = null;

        $response = $this->responseFactory->createResponse(200);
        try {
            $result = ($this->callable)($request, $response);
        } catch (\Throwable $e) {
            ob_get_clean();
            throw $e;
        } finally {
            while (ob_get_level() > $outputLevel + 1) {
                $output = ob_get_clean() . $output;
            }
        }

        return $this->wrapResponse(
            $response,
            $result,
            ob_get_clean() . $output
        );
    }

    /**
     * Convert endpoint result into valid response.
     *
     * @param Response $response Initial pipeline response.
     * @param mixed    $result   Generated endpoint output.
     * @param string   $output   Buffer output.
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
            $response->getBody()->write((string)$result);
        }

        //Always glue buffered output
        $response->getBody()->write($output);

        return $response;
    }
}
