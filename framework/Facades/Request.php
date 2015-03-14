<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Http\Message\StreamableInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Facade;
use Spiral\Components\Http\Request as HttpRequest;

/**
 * Attention, this facade will fail outside of HttpDispatcher->perform() scope.
 *
 * @method static HttpRequest castRequest(array $attributes = array())
 * @method static array getServerParams()
 * @method static array getCookieParams()
 * @method static HttpRequest withCookieParams(array $cookies)
 * @method static array getQueryParams()
 * @method static HttpRequest withQueryParams(array $query)
 * @method static array getFileParams()
 * @method static null|array|object getParsedBody()
 * @method static HttpRequest withParsedBody(array $data)
 * @method static array getAttributes()
 * @method static mixed getAttribute(string $name, mixed $default = null)
 * @method static HttpRequest withAttribute(string $name, mixed $value)
 * @method static HttpRequest withoutAttribute(string $name)
 * @method static bool isAjax()
 * @method static string remoteAddr()
 * @method static string getRequestTarget()
 * @method static HttpRequest withRequestTarget(mixed $requestTarget)
 * @method static string getMethod()
 * @method static HttpRequest withMethod(string $method)
 * @method static UriInterface getUri()
 * @method static HttpRequest withUri(UriInterface $uri)
 * @method static string getProtocolVersion()
 * @method static HttpRequest withProtocolVersion(string $version)
 * @method static array getHeaders()
 * @method static bool hasHeader(string $name, bool $normalize = true)
 * @method static string getHeader(string $name, bool $normalize = true)
 * @method static string[] getHeaderLines(string $name, bool $normalize = true)
 * @method static HttpRequest withHeader(string $name, mixed $value, bool $normalize = true)
 * @method static HttpRequest withAddedHeader(string $name, mixed $value, bool $normalize = true)
 * @method static HttpRequest withoutHeader(string $name, bool $normalize = true)
 * @method static StreamableInterface getBody()
 * @method static HttpRequest withBody(StreamableInterface $body)
 * @method static string getAlias()
 * @method static HttpRequest make(array $parameters = array())
 *
 * @method static HttpRequest\ParameterBag headers()
 * @method static HttpRequest\ServerBag    server()
 * @method static HttpRequest\ParameterBag cookies()
 * @method static HttpRequest\ParameterBag query()
 * @method static HttpRequest\ParameterBag post()
 * @method static HttpRequest\ParameterBag files()
 */
class Request extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'request';
}