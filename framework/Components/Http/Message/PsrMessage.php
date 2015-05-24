<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Spiral\Core\Component;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * Messages are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
abstract class PsrMessage extends Component implements MessageInterface
{
    /**
     * HTTP protocol version.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * Message headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Normalized (lowercased) set of header names. Required to correctly preserve header lookup.
     *
     * @invisible
     * @var array
     */
    protected $normalizedHeaders = array();

    /**
     * Message body.
     *
     * @var StreamInterface
     */
    protected $body = null;

    /**
     * Normalize set of headers to ensure it's values and normalized (lowercased) named.
     *
     * @param array $headers Headers to filter.
     * @return array Filtered headers and names.
     */
    protected function normalizeHeaders(array $headers)
    {
        $filteredHeaders = array();
        $normalizedNames = array();

        foreach ($headers as $header => $value)
        {
            if (!is_string($header))
            {
                continue;
            }

            if (!is_array($value) && !is_string($value))
            {
                continue;
            }

            $normalizedNames[strtolower($header)] = $header;
            $filteredHeaders[$header] = !is_array($value) ? array($value) : $value;
        }

        return [$filteredHeaders, $normalizedNames];
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $message = clone $this;
        $message->protocolVersion = $version;

        return $message;
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
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *                     name using a case-insensitive string comparison. Returns false if
     *                     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return array_key_exists(strtolower($name), $this->normalizedHeaders);
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
            return array();
        }

        $values = $this->headers[$this->normalizedHeaders[strtolower($name)]];

        return is_array($values) ? $values : array($values);
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *                     concatenated together using a comma. If the header does not appear in
     *                     the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $values = $this->getHeader($name);
        if (empty($values))
        {
            return null;
        }

        return join(',', $values);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        if (is_object($value))
        {
            $value = (string)$value;
        }

        if (!is_array($value) && !is_string($value))
        {
            throw new \InvalidArgumentException(
                'Invalid header value provided, only strings and arrays allowed.'
            );
        }

        if (!is_array($value))
        {
            $value = array($value);
        }

        $message = clone $this;
        $message->headers[$name] = $value;
        $message->normalizedHeaders[strtolower($name)] = $name;

        return $message;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        if (is_object($value))
        {
            $value = (string)$value;
        }

        if (!is_array($value) && !is_string($value))
        {
            throw new \InvalidArgumentException(
                'Invalid header value provided, only strings and arrays allowed.'
            );
        }

        if (!is_array($value))
        {
            $value = array($value);
        }

        if (!isset($this->headers[$name]))
        {
            return $this->withHeader($name, $value);
        }

        if (is_object($value))
        {
            $value = (string)$value;
        }

        if (!$this->hasHeader($name))
        {
            return $this->withHeader($name, $value);
        }

        $message = clone $this;
        $message->headers[$name] = array_merge($message->headers[$name], $value);

        return $message;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name))
        {
            return $this;
        }

        $normalized = strtolower($name);

        $original = $this->normalizedHeaders[$normalized];

        $message = clone $this;
        unset(
            $message->headers[$original],
            $message->normalizedHeaders[$normalized]
        );

        return $message;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $message = clone $this;
        $message->body = $body;

        return $message;
    }
}