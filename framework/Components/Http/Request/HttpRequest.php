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
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Components\Http\Message\HttpMessage;
use Spiral\Components\Http\Message\Stream;
use Spiral\Components\Http\Uri;
use Spiral\Core\Component;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class HttpRequest extends HttpMessage implements RequestInterface
{
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
     * @invisible
     * @var array
     */
    private $allowedMethods = array(
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
     * @param string|null            $method  Request method.
     * @param string|UriInterface    $uri     Requested URI.
     * @param string|StreamInterface $body    Request body or body stream.
     * @param array                  $headers Request headers.
     */
    public function __construct(
        $method = null,
        $uri = null,
        $body = 'php://memory',
        array $headers = array()
    )
    {
        if (!empty($method) && !in_array(strtoupper($method), $this->allowedMethods))
        {
            throw new \InvalidArgumentException("Unsupported HTTP method value provided.");
        }

        $this->method = $method;

        if (!empty($uri))
        {
            $this->uri = ($uri instanceof UriInterface) ? $uri : new Uri($uri);
        }

        $this->body = ($body instanceof StreamInterface) ? $body : new Stream($body);
        list($this->headers, $this->normalizedHeaders) = $this->normalizeHeaders($headers);
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        $result = $this->headers;
        if (!$this->hasHeader('host') && (!empty($this->uri) && $this->uri->getHost()))
        {
            $headers['Host'] = array($this->getUriHost());
        }

        return $result;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *                     header. If the header does not appear in the message, this method MUST
     *                     return an empty array.
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name))
        {
            if (strtolower($name) == 'host' && (!empty($this->uri) && $this->uri->getHost()))
            {
                return array($this->getUriHost());
            }

            return array();
        }

        return parent::getHeader($name);
    }

    /**
     * Fetch host value from uri.
     *
     * @return string
     */
    private function getUriHost()
    {
        $host = $this->uri->getHost();
        $host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';

        return $host;
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
        if (!empty($this->requestTarget))
        {
            return $this->requestTarget;
        }

        if (empty($this->uri))
        {
            return '/';
        }

        $target = $this->uri->getPath();
        if ($this->uri->getQuery())
        {
            $target .= '?' . $this->uri->getQuery();
        }

        if (empty($target))
        {
            $target = '/';
        }

        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
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
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        if (!empty($method) && !in_array(strtoupper($method), $this->allowedMethods))
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
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $request = clone $this;
        $request->uri = $uri;

        if ($preserveHost || !$uri->getHost())
        {
            return $request;
        }

        $host = $uri->getHost();
        if ($uri->getPort())
        {
            $host .= ':' . $uri->getPort();
        }

        return $request->withHeader('Host', $host);
    }
}