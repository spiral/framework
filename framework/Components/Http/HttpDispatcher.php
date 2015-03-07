<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\StreamableInterface;
use Spiral\Components\Debug\ExceptionSnapshot;
use Spiral\Components\Http\Response\JsonResponse;
use Spiral\Core\Component;
use Spiral\Core\Core;
use Spiral\Core\Dispatcher\ClientException;
use Spiral\Core\DispatcherInterface;
use Spiral\Helpers\StringHelper;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\RequestInterface as PsrRequest;

class HttpDispatcher extends Component implements DispatcherInterface
{
    /**
     * Letting dispatcher to control application flow and functionality.
     *
     * @param Core $core
     */
    public function start(Core $core)
    {
        $core->callAction('Controllers\HomeController');

        echo(StringHelper::formatBytes(memory_get_peak_usage()));

        //Cast request
        //pass to middleware(s) - this is where cookies processed, tokens checked and session handled, like big boys
        //MiddlewareRunner is required
        //perform
        //  route
        //  route specific dispatchers
        //  target controller/closure
        //dispatch
    }

    public function perform(PsrRequest $request)
    {
        //Create request scope ? or no?
        //if so, scope for what request type, only our? i think yes.

        if ($request instanceof RequestInterface)
        {
            //making scope, maybe make scope INSIDE route with route attached to request AS data chunk?
        }

        //perform, INNER MIDDLEWARE INSIDE ROUTE! i need RouterTrait! :)
        $response = null;

        //End request scope ? or no?
        if ($request instanceof RequestInterface)
        {
            //ending scope
        }

        return $this->wrapResponse($response);
    }

    protected function wrapResponse($response)
    {
        if ($response instanceof PsrResponse)
        {
            return $response;
        }

        //check situation where response is actually stream
        if ($response instanceof StreamableInterface)
        {
            //something like that
            return new Response($response);
        }

        if (is_array($response) || $response instanceof \JsonSerializable)
        {
            //Making json response
            return new JsonResponse($response); //something like this
        }

        //Making base response (string)
        return $response;
    }

    public function dispatch(PsrResponse $response, PsrRequest $request = null)
    {
        //do we need request here?

        //Sending headers and status

        //sending status

        foreach ($response->getHeaders() as $header)
        {
            //sending header
        }

        if ($response instanceof ResponseInterface)
        {
            //Sending cookies
            foreach ($response->getCookies() as $cookie)
            {
                //setcookie
            }
        }

        //Sending stream
        $stream = $response->getBody();
        if (!$stream->isSeekable())
        {
            echo $stream->getContents();
        }
        else
        {
            //seeking
        }

        exit();
    }

    /**
     * Every dispatcher should know how to handle exception snapshot provided by Debugger.
     *
     * @param ExceptionSnapshot $snapshot
     * @return mixed
     */
    public function handleException(ExceptionSnapshot $snapshot)
    {
        if ($snapshot->getException() instanceof ClientException)
        {
            //Simply showing something
            $this->dispatch(new Response('ERROR VIEW LAYOUT IF PRESENTED', $snapshot->getException()->getCode()));
        }

        echo $snapshot->renderSnapshot();
        //500 error OR snapshot, based on options
    }
}