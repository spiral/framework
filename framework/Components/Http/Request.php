<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamableInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Components\Http\Request\ParameterBag;
use Spiral\Components\Http\Request\HttpRequest;
use Spiral\Components\Http\Request\InputStream;
use Spiral\Components\Http\Request\ServerBag;
use Spiral\Components\Http\Request\Uri;

class Request extends HttpRequest implements ServerRequestInterface
{
    /**
     * The request "attributes" may be used to allow injection of any parameters derived from the
     * request: e.g., the results of path match operations; the results of decrypting cookies; the
     * results of deserializing non-form-encoded message bodies; etc. Attributes will be application
     * and request specific, and CAN be mutable.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Data related to the incoming request environment, typically derived from PHP's $_SERVER superglobal.
     *
     * @var array
     */
    protected $serverParams = array();

    /**
     * Cookies sent by the client to the server.
     *
     * @var array
     */
    protected $cookieParams = array();

    /**
     * The deserialized query string arguments, if any.
     *
     * @var array
     */
    protected $queryParams = array();

    /**
     * File upload metadata in the same structure as PHP's $_FILES superglobal.
     *
     * @var array
     */
    protected $fileParams = array();

    /**
     * Parameters provided in the request body. In most cases equals to _POST.
     *
     * @var array
     */
    protected $parsedBody = array();

    /**
     * New Server Request instance.
     *
     * @param string                     $method       Request method.
     * @param string|UriInterface        $uri          Requested URI.
     * @param string|StreamableInterface $body         Request body or body stream.
     * @param array                      $headers      Request headers, has to be normalized.
     * @param array                      $serverParams Data related to the incoming request
     *                                                 environment, typically derived from PHP's
     *                                                 $_SERVER superglobal.
     * @param array                      $cookieParams Cookies sent by the client to the server.
     * @param array                      $queryParams  The deserialized query string arguments, if any.
     * @param array                      $fileParams   File upload metadata in the same structure
     *                                                 as PHP's $_FILES superglobal.
     * @param array                      $parsedBody   Parameters provided in the request body. In most
     *                                                 cases equals to _POST.
     * @param array                      $attributes   Initial set of request attributes.
     */
    public function __construct(
        $method = null,
        $uri = null,
        $body = 'php://memory',
        array $headers = array(),
        array $serverParams = array(),
        array $cookieParams = array(),
        array $queryParams = array(),
        array $fileParams = array(),
        array $parsedBody = array(),
        array $attributes = array()
    )
    {
        parent::__construct($method, $uri, $body, $headers);

        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->fileParams = $fileParams;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
    }

    /**
     * Cast Server side requested based on global variables. $_SERVER and other global variables will
     * be used.
     *
     * @param array $attributes Initial set of attributes.
     * @return static
     */
    public static function castRequest(array $attributes = array())
    {
        $request = new static(
            $_SERVER['REQUEST_METHOD'],
            Uri::castUri($_SERVER),
            new InputStream(),
            self::castHeaders($_SERVER),
            $_SERVER,
            $_COOKIE,
            $_GET,
            $_FILES,
            $_POST,
            $attributes
        );

        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0')
        {
            $request->protocolVersion = '1.0';
        }
        else
        {
            $request->protocolVersion = '1.1';
        }

        return $request;
    }

    /**
     * Generate list of incoming headers. getallheaders() function will be used with fallback to
     * _SERVER array parsing.
     *
     * @param array $server
     * @return array
     */
    protected static function castHeaders(array $server)
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
                if ($name === 'HTTP_COOKIE')
                {
                    continue;
                }

                if (strpos($name, 'HTTP_') === 0)
                {
                    $headers[str_replace("_", "-", substr($name, 5))] = $value;
                }
            }
        }

        unset($headers['Cookie']);

        return $headers;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment, typically derived from PHP's
     * $_SERVER superglobal. The data IS NOT REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Create a new instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST be compatible with
     * the structure of $_COOKIE. Typically, this data will be injected at instantiation.
     *
     * This method MUST be implemented in such a way as to retain the immutability of the message,
     * and MUST return a new instance that has the new header and/or value.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return self
     */
    public function withCookieParams(array $cookies)
    {
        $request = clone $this;
        $request->cookieParams = $cookies;

        return $request;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URL or server params. If you need to
     * ensure you are only getting the original values, you may need to parse the composed URL or the
     * `QUERY_STRING` composed in the server params.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Create a new instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming request. They MAY be
     * injected during instantiation, such as from PHP's $_GET superglobal, or MAY be derived from
     * some other value such as the URI. In cases where the arguments are parsed from the URI, the
     * data MUST be compatible with what PHP's parse_str() would return for  purposes of how duplicate
     * query parameters are handled, and how nested sets are handled.
     *
     * Setting query string arguments MUST NOT change the URL stored by the request, nor the values
     * in the server params.
     *
     * This method MUST be implemented in such a way as to retain the immutability of the message,
     * and MUST return a new instance that has the new header and/or value.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     * @return self
     */
    public function withQueryParams(array $query)
    {
        $request = clone $this;
        $request->queryParams = $query;

        return $request;
    }

    /**
     * Retrieve the upload file metadata.
     *
     * This method MUST return file upload metadata in the same structure as PHP's $_FILES superglobal.
     *
     * These values MUST remain immutable over the course of the incoming request. They SHOULD be
     * injected during instantiation, such as from PHP's $_FILES superglobal, but MAY be derived from
     * other sources.
     *
     * @return array Upload file(s) metadata, if any.
     */
    public function getFileParams()
    {
        return $this->fileParams;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded or multipart/form-data,
     * and the request method is POST, this method MUST return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing the request body content; as
     * parsing returns structured content, the potential types MUST be arrays or objects only. A null
     * value indicates the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any. These will typically be an
     *                           array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Create a new instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the immutability of the message,
     * and MUST return a new instance that has the new header and/or value.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     * @return self
     */
    public function withParsedBody($data)
    {
        $request = clone $this;
        $request->parsedBody = $data;

        return $request;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Create a new instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the immutability of the message,
     * and MUST return a new instance that has the new header and/or value.
     *
     * @see getAttributes()
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     * @return self
     */
    public function withAttribute($name, $value)
    {
        $request = clone $this;
        $request->attributes[$name] = $value;

        return $request;
    }

    /**
     * Create a new instance that removes the specified derived request
     * attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the immutability of the message,
     * and MUST return a new instance that has the new header and/or value.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return self
     */
    public function withoutAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes))
        {
            //Untouched
            return $this;
        }

        $request = clone $this;
        unset($request->attributes[$name]);

        return $request;
    }
}