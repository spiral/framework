<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamableInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Components\Http\Message\MessageTrait;
use Spiral\Components\Http\Message\Stream;
use Spiral\Core\Component;

class BaseRequest extends Component implements RequestInterface
{
    /**
     * Common http message methods.
     */
    use MessageTrait;

    /**
     * The message's request target.
     *
     * @var null|string
     */
    protected $requestTarget = null;

    /**
     * HTTP method of the request.
     *
     * @var string|null
     */
    protected $method = null;

    /**
     * UriInterface instance representing the URI of the request.
     *
     * @var UriInterface
     */
    protected $uri = null;

    /**
     * Allowed (supported) HTTP methods.
     *
     * @var array
     */
    protected $allowedMethods = array(
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE'
    );

    /**
     * New Request instance.
     *
     * @param string|UriInterface        $uri       Requested URI.
     * @param string                     $method    Request method.
     * @param string|StreamableInterface $body      Request body or body stream.
     * @param array                      $headers   Request headers.
     * @param bool                       $normalize Normalize headers case (disabled by default).
     */
    public function __construct(
        $uri = null,
        $method = null,
        $body = 'php://memory',
        array $headers = array(),
        $normalize = true
    )
    {
        if (!is_null($method) && !in_array(strtoupper($method), $this->allowedMethods))
        {
            throw new \InvalidArgumentException("Unsupported HTTP method value provided.");
        }

        if (!is_null($uri))
        {
            $this->uri = ($uri instanceof UriInterface) ? $uri : new Uri($uri);
        }

        $this->method = $method;
        $this->body = ($body instanceof StreamableInterface) ? $body : new Stream($body);

        //        if ($normalize)
        //        {
        //        }
        //        else
        //        {
        //        }

        //headers
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget)
        {
            return $this->requestTarget;
        }

        if (!$this->uri)
        {
            return '/';
        }

        $query = $this->uri->getQuery();

        return $this->uri->getPath() . ($query ? '?' . $query : '');
    }

    /**
     * Create a new instance with a specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('/\s/', $requestTarget))
        {
            throw new \InvalidArgumentException(
                'Invalid request target value, no whitespaces allowed.'
            );
        }

        $request = clone $this;
        $request->requestTarget = $requestTarget;

        return $request;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Create a new instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request method.
     *
     * @param string $method Case-insensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        if (!is_null($method) && !in_array(strtoupper($method), $this->allowedMethods))
        {
            throw new \InvalidArgumentException("Unsupported HTTP method value provided.");
        }

        $request = clone $this;
        $request->method = $method;

        return $request;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request, if any.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Create a new instance with the provided URI.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @return self
     */
    public function withUri(UriInterface $uri)
    {
        $request = clone $this;
        $request->uri = $uri;

        return $request;
    }
}