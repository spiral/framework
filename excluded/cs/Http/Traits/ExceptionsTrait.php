<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Traits;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Views\ViewsInterface;

/**
 * Provides ability to render client information into file.
 */
trait ExceptionsTrait
{
    use JsonTrait;

    /**
     * Write ClientException content into response.
     *
     * @param Request         $request
     * @param Response        $response
     * @param ClientException $exception
     * @param HttpConfig      $config
     * @param ViewsInterface  $views
     * @return Request
     */
    private function writeException(
        Request $request,
        Response $response,
        ClientException $exception,
        HttpConfig $config,
        ViewsInterface $views = null
    ) {
        //Has to contain valid http code
        $response = $response->withStatus($exception->getCode());

        if ($request->getHeaderLine('Accept') == 'application/json') {
            //Json got requested
            return $this->writeJson($response, ['status' => $exception->getCode()]);
        }

        if (!$config->hasView($exception->getCode()) || empty($views)) {
            //We don't or can't render http error view
            return $response;
        }

        $errorPage = $views->render($config->errorView($exception->getCode()), [
            'httpConfig' => $config,
            'request'    => $request
        ]);

        $response->getBody()->write($errorPage);

        return $response;
    }
}