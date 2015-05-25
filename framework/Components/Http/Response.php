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
use Psr\Http\Message\StreamInterface;
use Spiral\Components\Http\Message\HttpMessage;
use Spiral\Components\Http\Response\StringStream;
use Spiral\Core\Component;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends HttpMessage implements ResponseInterface
{
    /**
     * Default set of http codes.
     */
    const SUCCESS           = 200;
    const CREATED           = 201;
    const ACCEPTED          = 202;
    const BAD_REQUEST       = 400;
    const UNAUTHORIZED      = 401;
    const FORBIDDEN         = 403;
    const NOT_FOUND         = 404;
    const SERVER_ERROR      = 500;
    const REDIRECT          = 307;
    const MOVED_PERMANENTLY = 301;

    /**
     * Status code headers.
     *
     * @invisible
     * @var array
     */
    protected $reasonPhrases = array(
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
     * New immutable response instance. Content can be provided
     *
     * @param string|resource|StreamInterface $content String content or resource.
     * @param int                             $status
     * @param array                           $headers
     */
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        if (is_string($content))
        {
            $this->body = new StringStream($content);
        }
        elseif ($content instanceof StreamInterface)
        {
            $this->body = $content;
        }
        elseif (is_resource($content))
        {
            $this->body = new Stream($content, 'wb+');
        }
        else
        {
            throw new \InvalidArgumentException(
                "Invalid content value, only strings and StreamInterface allowed."
            );
        }

        $this->statusCode = (int)$status;
        $this->reasonPhrase = $this->getPhrase($status);

        list($this->headers, $this->normalizedHeaders) = $this->normalizeHeaders($headers);
    }

    /**
     * Helper method to retrieve status phrase based on status code.
     *
     * @param int $status
     * @return string|null
     */
    protected function getPhrase($status)
    {
        if (isset($this->reasonPhrases[$status]))
        {
            return $this->reasonPhrases[$status];
        }

        return null;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $code = (int)$code;
        if ($code < 200 || $code > 600)
        {
            throw new \InvalidArgumentException(
                "Invalid status code value, expected integer 200-599. Got {$code}."
            );
        }

        $message = clone $this;
        $message->statusCode = (int)$code;
        $message->reasonPhrase = !empty($reasonPhrase) ? $reasonPhrase : $this->getPhrase($code);

        return $message;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return !empty($this->reasonPhrase) ? $this->reasonPhrase : '';
    }
}