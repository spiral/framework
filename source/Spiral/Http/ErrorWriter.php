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
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Traits\JsonTrait;
use Spiral\Views\ViewsInterface;

/**
 * Provides ability to write exception or snapshot information into response.
 */
class ErrorWriter
{
    use JsonTrait;

    /**
     * @var HttpConfig
     */
    private $config = null;

    /**
     * @var ViewsInterface
     */
    protected $views = null;

    /**
     * @param HttpConfig     $config
     * @param ViewsInterface $views
     */
    public function __construct(HttpConfig $config, ViewsInterface $views)
    {
        $this->config = $config;
        $this->views = $views;
    }

    /**
     * Write ClientException content into response.
     *
     * @param Request         $request
     * @param Response        $response
     * @param ClientException $exception
     *
     * @return Response
     */
    public function writeException(
        Request $request,
        Response $response,
        ClientException $exception
    ): Response {
        //Has to contain valid http code
        $response = $response->withStatus($exception->getCode());

        if ($request->getHeaderLine('Accept') == 'application/json') {
            //Json got requested
            return $this->writeJson($response, ['status' => $exception->getCode()]);
        }

        if (!$this->config->hasView($exception->getCode())) {
            //We don't or can't render http error view
            return $response;
        }

        //Generating error page using view file specified in a config
        $errorPage = $this->views->render(
            $this->config->errorView($exception->getCode()),
            ['httpConfig' => $this->config, 'request' => $request]
        );

        $response->getBody()->write($errorPage);

        return $response;
    }

    /**
     * Write snapshot content into exception.
     *
     * @param Request           $request
     * @param Response          $response
     * @param SnapshotInterface $snapshot
     *
     * @return Response
     */
    public function writeSnapshot(
        Request $request,
        Response $response,
        SnapshotInterface $snapshot
    ): Response {
        //Exposing exception
        if ($request->getHeaderLine('Accept') != 'application/json') {
            $response->getBody()->write($snapshot->render());

            //Normal exception page
            return $response->withStatus(ClientException::ERROR);
        }

        //Exception in a form of JSON object
        return $this->writeJson($response, $snapshot->describe(), ClientException::ERROR);
    }
}