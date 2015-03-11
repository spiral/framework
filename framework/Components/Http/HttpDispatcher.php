<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Components\Debug\Snapshot;
use Spiral\Components\Http\Request\InputStream;
use Spiral\Components\Http\Request\Uri;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Spiral\Core\Dispatcher\ClientException;
use Spiral\Core\DispatcherInterface;

class HttpDispatcher extends Component implements DispatcherInterface
{
    /**
     * Required traits.
     */
    use Component\SingletonTrait, Component\LoggerTrait, Component\EventsTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'http';

    /**
     * Core instance.
     *
     * @invisible
     * @var Core
     */
    protected $core = null;

    /**
     * Original server request generated by spiral while starting HttpDispatcher.
     *
     * @var Request
     */
    protected $baseRequest = null;

    /**
     * New HttpDispatcher instance.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
        $this->config = $core->loadConfig('http');
    }

    /**
     * Letting dispatcher to control application flow and functionality.
     *
     * @param Core $core
     */
    public function start(Core $core)
    {
        //Initial server request
        $this->baseRequest = $this->castRequest();

        //Middleware(s)
        $response = $this->perform($this->baseRequest);

        $this->dispatch($response);
    }

    /**
     * Get initial request generated by HttpDispatcher. This is untouched request object, all cookies will be encrypted
     * and other values will not be pre-processed.
     *
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->baseRequest;
    }

    /**
     * Cast Server side requested based on global variables.
     *
     * @return Request
     */
    protected function castRequest()
    {
        return Request::make(array(
            'uri'          => Uri::castUri($_SERVER),
            'method'       => $_SERVER['REQUEST_METHOD'],
            'body'         => new InputStream(),
            'headers'      => $this->castHeaders($_SERVER),
            'serverParams' => $_SERVER,
            'cookieParams' => $_COOKIE,
            'queryParams'  => $_GET,
            'fileParams'   => $_FILES,
            'parsedBody'   => $_POST,
            'normalize'    => false
        ));
    }

    public function perform(RequestInterface $request = null)
    {
        $parentRequest = Container::getBinding('request');
        if ($request)
        {
            //Creating scope
            Container::bind('request', $request);
            Container::bind(get_class($request), $request);
        }
        else
        {
            Container::removeBinding('request');
            $parentRequest && Container::removeBinding(get_class($parentRequest));
        }

        ob_start();

        //Routing is happening here
        $response = $this->core->callAction('Controllers\HomeController', 'index');
        $plainOutput = ob_get_clean();

        if ($request)
        {
            //Ending scope
            Container::removeBinding('request');
            Container::removeBinding(get_class($request));
        }

        if ($parentRequest)
        {
            //Restoring scope
            Container::bind('request', $parentRequest);
            Container::bind(get_class($parentRequest), $parentRequest);
        }

        return $this->wrapResponse($response, $plainOutput);
    }

    protected function wrapResponse($response, $plainOutput = '')
    {
        if ($response instanceof ResponseInterface)
        {
            $plainOutput && $response->getBody()->write($plainOutput);

            return $response;
        }

        //        if (is_array($response) || $response instanceof \JsonSerializable)
        //        {
        //            //Making json response
        //            //return new JsonResponse($response); //something like this
        //        }

        return new Response($response . $plainOutput);
    }

    /**
     * Dispatch provided request to client. Application will stop after this method call.
     *
     * @param ResponseInterface $response
     */
    public function dispatch(ResponseInterface $response)
    {
        while (ob_get_level())
        {
            ob_get_clean();
        }

        //$statusHeader = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()}";
        //header(rtrim("{$statusHeader} {$response->getReasonPhrase()}"));

        //Receive all headers but not cookies
        foreach ($response->getHeaders() as $header => $values)
        {
            $replace = true;
            foreach ($values as $value)
            {
                header("{$header}: {$value}", $replace);
                $replace = false;
            }
        }

        //Spiral request stores cookies separately with headers to make them easier to send
        if ($response instanceof Response)
        {
            foreach ($response->getCookies() as $cookie)
            {
                //TODO: Default cookie domain!
                setcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpire(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->getSecure(),
                    $cookie->getHttpOnly()
                );
            }
        }

        if ($response->getStatusCode() == 204)
        {
            return;
        }

        $stream = $response->getBody();

        // I need self sending requests in future.
        if (!$stream->isSeekable())
        {
            echo (string)$stream;
        }
        else
        {
            ob_implicit_flush(true);
            $stream->rewind();
            while (!$stream->eof())
            {
                echo $stream->read(1024);
            }
        }
    }

    //    /**
    //     * Generate response to represent specified error code. Response can include pure headers or may have attached view
    //     * file (based on HttpDispatcher configuration).
    //     *
    //     * @param int $code
    //     * @return ResponseInterface|Response
    //     */
    //    protected function errorResponse($code)
    //    {
    //        //todo: implement
    //    }

    /**
     * Every dispatcher should know how to handle exception snapshot provided by Debugger.
     *
     * @param Snapshot $snapshot
     * @return mixed
     */
    public function handleException(Snapshot $snapshot)
    {
        if ($snapshot->getException() instanceof ClientException)
        {
            //Simply showing something
            //$this->dispatch(new Response('ERROR VIEW LAYOUT IF PRESENTED', $snapshot->getException()->getCode()));
        }

        //TODO: hide snapshot based on config
        $this->dispatch(new Response($snapshot->renderSnapshot(), 500));
    }

    /**
     * Generate list of incoming headers. getallheaders() function will be used with fallback to _SERVER array parsing.
     *
     * @param array $server
     * @return array
     */
    protected function castHeaders(array $server)
    {
        if (function_exists('getallheaders'))
        {
            $headers = getallheaders();
        }
        else
        {
            $headers = array();
            foreach ($server as $name => $value)
            {
                if ($name == 'HTTP_COOKIE')
                {
                    continue;
                }

                if (strpos($name, 'HTTP_') === 0)
                {
                    $name = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }
        unset($headers['Cookie']);

        return $headers;
    }
}