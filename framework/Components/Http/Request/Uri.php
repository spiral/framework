<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * The scheme of the URI.
     *
     * @var string
     */
    private $scheme = '';

    /**
     * Authority portion of the URI, in "[user-info@]host[:port]" format.
     *
     * @var string
     */
    private $userInfo = '';

    /**
     * Host segment of the URI.
     *
     * @var string
     */
    private $host = '';

    /**
     * Host segment of the URI.
     *
     * @var int
     */
    private $port = 80;

    /**
     * The path segment of the URI.
     *
     * @var string
     */
    private $path = '';

    /**
     * The URI query string.
     *
     * @var string
     */
    private $query = '';

    /**
     * The URI fragment.
     *
     * @var string
     */
    private $fragment = '';

    /**
     * Set of supported Uri schemes and their ports.
     *
     * @invisible
     * @var array
     */
    private $defaultSchemes = array(
        'http'  => 80,
        'https' => 443
    );

    /**
     * Create new Uri instance based on provided Uri string. All Uri object properties declared as
     * private to respect immutability or Uri.
     *
     * @param string $uri
     */
    public function __construct($uri = '')
    {
        if (!empty($uri))
        {
            $this->parseUri($uri);
        }
    }

    /**
     * Parse income uri and populate instance values.
     *
     * @param string $uri
     */
    private function parseUri($uri)
    {
        $components = parse_url($uri);

        $this->scheme = isset($components['scheme']) ? $components['scheme'] : '';
        $this->host = isset($components['host']) ? $components['host'] : '';
        $this->port = isset($components['port']) ? $components['port'] : null;
        $this->path = isset($components['path']) ? $components['path'] : '/';
        $this->query = isset($components['query']) ? $components['query'] : '';
        $this->fragment = isset($components['fragment']) ? $components['fragment'] : '';

        if (isset($components['pass']))
        {
            $this->userInfo = $components['user'] . ':' . $components['pass'];
        }
        elseif (isset($components['user']))
        {
            $this->userInfo = $components['user'];
        }
    }

    /**
     * Cast Uri object properties based on values provided in server array ($_SERVER).
     *
     * @param array $server
     * @return static
     */
    public static function castUri(array $server)
    {
        $uri = new static;

        $uri->scheme = 'http';
        if (isset($server['HTTPS']) && $server['HTTPS'] == 'on')
        {
            $uri->scheme = 'https';
        }
        elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] == 'https')
        {
            $uri->scheme = 'https';
        }
        elseif (isset($server['HTTP_X_FORWARDED_SSL']) && $server['HTTP_X_FORWARDED_SSL'] == 'on')
        {
            $uri->scheme = 'https';
        }

        if (isset($server['SERVER_PORT']))
        {
            $uri->port = (int)$server['SERVER_PORT'];
        }

        if (isset($server['HTTP_HOST']))
        {
            $uri->host = $server['HTTP_HOST'];
            if ($delimiter = strpos($server['HTTP_HOST'], ':'))
            {
                $uri->port = (int)substr($uri->host, $delimiter + 1);
                $uri->host = substr($uri->host, 0, $delimiter);
            }
        }
        elseif (isset($server['HTTP_NAME']))
        {
            $uri->host = $server['HTTP_NAME'];
        }

        if (isset($server['UNENCODED_URL']))
        {
            $uri->path = $server['UNENCODED_URL'];
        }
        elseif (isset($server['REQUEST_URI']))
        {
            $uri->path = $server['REQUEST_URI'];
        }
        elseif (isset($server['HTTP_X_REWRITE_URL']))
        {
            $uri->path = $server['HTTP_X_REWRITE_URL'];
        }
        elseif (isset($server['HTTP_X_ORIGINAL_URL']))
        {
            $uri->path = $server['HTTP_X_ORIGINAL_URL'];
        }

        if (($query = strpos($uri->path, '?')) !== false)
        {
            $uri->path = substr($uri->path, 0, $query);
        }

        $uri->path = $uri->path ?: '/';
        if (isset($server['QUERY_STRING']))
        {
            $uri->query = ltrim($server['QUERY_STRING'], '?');
        }

        return $uri;
    }

    /**
     * Retrieve the URI scheme.
     *
     * Implementations SHOULD restrict values to "http", "https", or an empty string but MAY accommodate
     * other schemes if required.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The string returned MUST omit the trailing "://" delimiter if present.
     *
     * @return string The scheme of the URI.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority portion of the URI.
     *
     * The authority portion of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current scheme, it SHOULD NOT
     * be included.
     *
     * This method MUST return an empty string if no authority information is present.
     *
     * @return string Authority portion of the URI, in "[user-info@]host[:port]"
     *     format.
     */
    public function getAuthority()
    {
        if (empty($this->host))
        {
            return '';
        }

        $result = ($this->userInfo ? $this->userInfo . '@' : '') . $this->host;

        if ($this->port && !$this->isDefaultPort())
        {
            $result .= ':' . $this->port;
        }

        return $result;
    }

    /**
     * Retrieve the user information portion of the URI, if present.
     *
     * If a user is present in the URI, this will return that value; additionally, if the password is
     * also present, it will be appended to the user value, with a colon (":") separating the values.
     *
     * Implementations MUST NOT return the "@" suffix when returning this value.
     *
     * @return string User information portion of the URI, if present, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Retrieve the host segment of the URI.
     *
     * This method MUST return a string; if no host segment is present, an empty string MUST be returned.
     *
     * @return string Host segment of the URI.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the port segment of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme, this method MUST return
     * it as an integer. If the port is the standard port used with the current scheme, this method
     * SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return the standard port for
     * that scheme, but SHOULD return null.
     *
     * @return null|int Host segment of the URI.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Check if current port default for current scheme.
     *
     * @return bool
     */
    private function isDefaultPort()
    {
        return !$this->scheme || $this->defaultSchemes[$this->scheme] == $this->port;
    }

    /**
     * Retrieve the path segment of the URI.
     *
     * This method MUST return a string; if no path is present it MUST return an empty string.
     *
     * @return string The path segment of the URI.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * This method MUST return a string; if no query string is present, it MUST return an empty string.
     *
     * The string returned MUST omit the leading "?" character.
     *
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment segment of the URI.
     *
     * This method MUST return a string; if no fragment is present, it MUST return an empty string.
     *
     * The string returned MUST omit the leading "#" character.
     *
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Create a new instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified scheme. If the scheme provided includes the "://" delimiter, it MUST be
     * removed.
     *
     * Implementations SHOULD restrict values to "http", "https", or an empty string but MAY accommodate
     * other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        if (!empty($scheme))
        {
            if (strpos($scheme, '://'))
            {
                $scheme = substr($scheme, 0, -3);
            }

            if (!isset($this->defaultSchemes[$scheme]))
            {
                throw new \InvalidArgumentException(
                    'Invalid scheme value, only "http" and "allowed".'
                );
            }
        }

        $uri = clone $this;
        $uri->scheme = $scheme;

        return $uri;
    }

    /**
     * Create a new instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified user information.
     *
     * Password is optional, but the user information MUST include the user; an empty string for the
     * user is equivalent to removing user information.
     *
     * @param string      $user     User name to use for authority.
     * @param null|string $password Password associated with $user.
     * @return static               A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $uri = clone $this;
        $uri->userInfo = $user . ($password ? ':' . $password : '');

        return $uri;
    }

    /**
     * Create a new instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host Hostname to use with the new instance.
     * @return static      A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $uri = clone $this;
        $uri->host = $host;

        return $uri;
    }

    /**
     * Create a new instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port information.
     *
     * @param null|int $port Port to use with the new instance; a null value removes the port information.
     * @return static        A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        if (!is_null($port))
        {
            $port = (int)$port;
            if ($port < 1 || $port > 65535)
            {
                throw new \InvalidArgumentException(
                    'Invalid port value, use only TCP and UDP range.'
                );
            }
        }

        $uri = clone $this;
        $uri->port = $port;

        return $uri;
    }

    /**
     * Create a new instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified path.
     *
     * The path MUST be prefixed with "/"; if not, the implementation MAY provide the prefix itself.
     *
     * An empty path value is equivalent to removing the path.
     *
     * @param string $path The path to use with the new instance.
     * @return static      A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        if (!empty($path))
        {
            if (strpos($path, '?') !== false || strpos($path, '#') !== false)
            {
                throw new \InvalidArgumentException(
                    'Invalid path value, path must not include URI query of URI fragment.'
                );
            }

            if ($path[0] !== '/')
            {
                $path = '/' . $path;
            }
        }
        else
        {
            $path = '/';
        }

        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * Create a new instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified query string.
     *
     * If the query string is prefixed by "?", that character MUST be removed. Additionally, the query
     * string SHOULD be parseable by parse_str() in order to be valid.
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return static       A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        if (!empty($query))
        {
            if (strpos($query, '#') !== false)
            {
                throw new \InvalidArgumentException(
                    'Invalid query value, query must not URI fragment.'
                );
            }

            if ($query[0] == '?')
            {
                $query = substr($query, 1);
            }
        }
        else
        {
            $query = '';
        }

        $uri = clone $this;
        $uri->query = $query;

        return $uri;
    }

    /**
     * Create a new instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return a new instance that
     * contains the specified URI fragment.
     *
     * If the fragment is prefixed by "#", that character MUST be removed.
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The URI fragment to use with the new instance.
     * @return static          A new instance with the specified URI fragment.
     */
    public function withFragment($fragment)
    {
        if (!empty($fragment))
        {
            if ($fragment[0] == '#')
            {
                $fragment = substr($fragment, 1);
            }
        }
        else
        {
            $fragment = '';
        }

        $uri = clone $this;
        $uri->fragment = $fragment;

        return $uri;
    }

    /**
     * Return the string representation of the URI.
     *
     * Concatenates the various segments of the URI, using the appropriate delimiters:
     *
     * - If a scheme is present, "://" MUST append the value.
     * - If the authority information is present, that value will be concatenated.
     * - If a path is present, it MUST be prefixed by a "/" character.
     * - If a query string is present, it MUST be prefixed by a "?" character.
     * - If a URI fragment is present, it MUST be prefixed by a "#" character.
     *
     * @return string
     */
    public function toString()
    {
        $result = ($this->scheme ? $this->scheme . '://' : '');

        //UserInfo + host + port + path
        $result .= $this->getAuthority() . $this->path;

        if (!empty($this->query))
        {
            $result .= '?' . $this->query;
        }

        if (!empty($this->fragment))
        {
            $result .= '#' . $this->fragment;
        }

        return $result;
    }

    /**
     * Return the string representation of the URI.
     *
     * Concatenates the various segments of the URI, using the appropriate delimiters:
     *
     * - If a scheme is present, "://" MUST append the value.
     * - If the authority information is present, that value will be concatenated.
     * - If a path is present, it MUST be prefixed by a "/" character.
     * - If a query string is present, it MUST be prefixed by a "?" character.
     * - If a URI fragment is present, it MUST be prefixed by a "#" character.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}