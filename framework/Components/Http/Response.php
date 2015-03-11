<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamableInterface;
use Spiral\Components\Http\Message\MessageTrait;
use Spiral\Components\Http\Message\Stream;
use Spiral\Components\Http\Response\CookieInterface;
use Spiral\Core\Component;

class Response extends Component implements ResponseInterface
{
    /**
     * Common http message methods.
     */
    use MessageTrait;

    /**
     * Status code headers.
     *
     * @var array
     */
    protected static $phrases = array(
        //Technical
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",

        //Success
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",

        //Redirects
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        306 => "(Unused)",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",

        //Client errors
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        419 => "Authentication Timeout",
        420 => "Enhance Your Calm",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        425 => "Unordered Collection",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        444 => "No Response",
        449 => "Retry With",
        450 => "Blocked by Windows Parental Controls",
        451 => "Unavailable For Legal Reasons",
        494 => "Request Header Too Large",
        495 => "Cert Error",
        496 => "No Cert",
        497 => "HTTP to HTTPS",
        499 => "Client Closed Request",

        //Server errors
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        508 => "Loop Detected",
        509 => "Bandwidth Limit Exceeded",
        510 => "Not Extended",
        511 => "Network Authentication Required",
        598 => "Network read timeout error",
        599 => "Network connect timeout error"
    );

    /**
     * The response Status-Code.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * The response Reason-Phrase, a short textual description of the Status-Code.
     *
     * @var null|string
     */
    protected $reasonPhrase = null;

    /**
     * Cookies has to be send to client.
     *
     * @var CookieInterface[]
     */
    protected $cookies = array();

    /**
     * New immutable response instance. Content can be provided
     *
     * @param string|StreamableInterface $content   String content or string.
     * @param int                        $statusCode
     * @param array                      $headers
     * @param bool                       $normalize Normalize headers case (disabled by default).
     */
    public function __construct(
        $content = '',
        $statusCode = 200,
        array $headers = array(),
        $normalize = true
    )
    {
        if (is_string($content))
        {
            $this->body = new Stream('php://memory', 'w');
            $this->body->write($content);
        }
        elseif ($content instanceof StreamableInterface)
        {
            $this->body = $content;
        }
        else
        {
            throw new \InvalidArgumentException(
                "Invalid content value, only strings and StreamableInterface allowed."
            );
        }

        $this->setStatusCode($statusCode);
        $this->headers = $this->prepareHeaders($headers, $normalize);
    }

    /**
     * Helper method to set status code and validate it's value.
     *
     * @param int $code
     */
    protected function setStatusCode($code)
    {
        $code = (int)$code;
        if ($code < 200 || $code > 600)
        {
            throw new \InvalidArgumentException(
                "Invalid status code value, expected integer 200-599."
            );
        }

        $this->statusCode = $code;
        $this->reasonPhrase = null;

        if (isset(self::$phrases[$this->statusCode]))
        {
            $this->reasonPhrase = self::$phrases[$this->statusCode];
        }
    }

    /**
     * Gets the response Status-Code.
     *
     * The Status-Code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return integer Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Create a new instance with the specified status code, and optionally
     * reason phrase, for the response.
     *
     * If no Reason-Phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * Status-Code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param integer     $code         The 3-digit integer result code to set.
     * @param null|string $reasonPhrase The reason phrase to use with the
     *                                  provided status code; if none is provided, implementations MAY
     *                                  use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        $response = clone $this;
        $response->setStatusCode($code);
        $reasonPhrase && $response->reasonPhrase = $reasonPhrase;

        return $response;
    }

    /**
     * Gets the response Reason-Phrase, a short textual description of the Status-Code.
     *
     * Because a Reason-Phrase is not a required element in a response
     * Status-Line, the Reason-Phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * Status-Code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string|null Reason phrase, or null if unknown.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Create a new instance with the scheduled cookie instance, cookie will be
     * sent to client in HttpDispatcher->dispatch() method.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @see setcookie()
     * @param CookieInterface $cookie Cookie instance.
     * @return self
     */
    public function withCookie(CookieInterface $cookie)
    {
        $response = clone $this;
        $response->cookies[$cookie->getName()] = $cookie;

        return $response;
    }

    /**
     * Create a new instance without the scheduled cookie instance, cookie will be
     * sent to client in HttpDispatcher->dispatch() method.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @see setcookie()
     * @param string $name Cookie name.
     * @return self
     */
    public function withoutCookie($name)
    {
        if (!isset($this->cookies[$name]))
        {
            return $this;
        }

        $response = clone $this;
        unset($response->cookies[$name]);

        return $response;
    }

    /**
     * Create a new instance with replaced array of scheduled cookie instances,
     * cookies will be sent to client in HttpDispatcher->dispatch() method.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @see setcookie()
     * @param CookieInterface[] $cookies
     * @return self
     * @throws \InvalidArgumentException For invalid cookie item.
     */
    public function withCookies(array $cookies)
    {
        $response = clone $this;
        $response->cookies = array();
        foreach ($cookies as $cookie)
        {
            if (!$cookie instanceof CookieInterface)
            {
                throw new \InvalidArgumentException(
                    "Cookies array should contain only CookieInterface instanced."
                );
            }

            $response->cookies[$cookie->getName()] = $cookie;
        }

        return $response;
    }

    /**
     * Get all cookies associated with response. Cookies will be send in HttpDispatcher->dispatch() method.
     *
     * @return CookieInterface[]
     */
    public function getCookies()
    {
        return $this->cookies;
    }
}