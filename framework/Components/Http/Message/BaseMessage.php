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
use Psr\Http\Message\StreamableInterface;
use Spiral\Core\Component;

abstract class BaseMessage extends Component implements MessageInterface
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
     * Message body.
     *
     * @var StreamableInterface
     */
    protected $body = null;

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
     * Create a new instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
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
     * Retrieves all message headers.
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
     *     key MUST be a header name, and each value MUST be an array of strings.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name      Case-insensitive header field name.
     * @param bool   $normalize Normalize header name.
     * @return bool Returns true if any header names match the given header
     *                          name using a case-insensitive string comparison. Returns false if
     *                          no matching header name is found in the message.
     */
    public function hasHeader($name, $normalize = true)
    {
        return array_key_exists($this->normalizeHeader($name, $normalize), $this->headers);
    }

    /**
     * Retrieve a header by the given case-insensitive name, as a string.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeaderLines() instead
     * and supply your own delimiter when concatenating.
     *
     * @param string $name      Case-insensitive header field name.
     * @param bool   $normalize Normalize header name.
     * @return string
     */
    public function getHeader($name, $normalize = true)
    {
        return join(',', $this->getHeaderLines($name, $normalize));
    }

    /**
     * Retrieves a header by the given case-insensitive name as an array of strings.
     *
     * @param string $name      Case-insensitive header field name.
     * @param bool   $normalize Normalize header name.
     * @return string[]
     */
    public function getHeaderLines($name, $normalize = true)
    {
        $name = $this->normalizeHeader($name, $normalize);
        if (!$this->hasHeader($name, false))
        {
            return array();
        }

        return $this->headers[$name];
    }

    /**
     * Create a new instance with the provided header, replacing any existing
     * values of any headers with the same case-insensitive name.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name      Case-insensitive header field name.
     * @param string|string[] $value     Header value(s).
     * @param bool            $normalize Normalize header name.
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value, $normalize = true)
    {
        $name = $this->normalizeHeader($name, $normalize);

        if (!is_array($value) && !is_string($value))
        {
            throw new \InvalidArgumentException(
                'Invalid header value provided, only strings and arrays allowed.'
            );
        }

        if (is_string($value))
        {
            $value = array($value);
        }

        $message = clone $this;
        $message->headers[$name] = $value;

        return $message;
    }

    /**
     * Creates a new instance, with the specified header appended with the
     * given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new header and/or value.
     *
     * @param string          $name      Case-insensitive header field name to add.
     * @param string|string[] $value     Header value(s).
     * @param bool            $normalize Normalize header name.
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value, $normalize = true)
    {
        $name = $this->normalizeHeader($name, $name);

        if (!$this->hasHeader($name, false))
        {
            return $this->withHeader($name, $value, false);
        }

        if (!is_array($value) && !is_string($value))
        {
            throw new \InvalidArgumentException(
                'Invalid header value provided, only strings and arrays allowed.'
            );
        }

        if (is_string($value))
        {
            $value = array($value);
        }

        $message = clone $this;
        $message->headers[$name] = array_merge($message->headers[$name], $value);

        return $message;
    }

    /**
     * Creates a new instance, without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that removes
     * the named header.
     *
     * @param string $name      Case-insensitive header field name to remove.
     * @param bool   $normalize Normalize header name.
     * @return self
     */
    public function withoutHeader($name, $normalize = true)
    {
        $name = $this->normalizeHeader($name, $normalize);
        if (!$this->hasHeader($name, false))
        {
            return $this;
        }

        $message = clone $this;
        unset($message->headers[$name]);

        return $message;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamableInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Create a new instance, with the specified message body.
     *
     * The body MUST be a StreamableInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamableInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamableInterface $body)
    {
        $message = clone $this;
        $message->body = $body;

        return $message;
    }

    /**
     * Ensure that header is in valid format.
     *
     * @param string $header
     * @param bool   $normalize Apply normalization.
     * @return string
     */
    protected function normalizeHeader($header, $normalize = true)
    {
        if (!$normalize)
        {
            return $header;
        }

        return str_replace(' ', '-', ucwords(str_replace('-', ' ', $header)));
    }

    /**
     * Store header values in array form as requested by PSR7. Additionally this method can normalize
     * header names.
     *
     * @param array $headers
     * @param bool  $normalize Apply normalization to header names.
     * @return array
     */
    protected function prepareHeaders(array $headers, $normalize = true)
    {
        $result = [];
        foreach ($headers as $header => $value)
        {
            if (!is_string($header) || (!is_string($value) && !is_array($value)))
            {
                continue;
            }

            if (is_string($value))
            {
                $value = array($value);
            }

            if ($normalize)
            {
                $result[$this->normalizeHeader($header)] = $value;
            }
            else
            {
                $result[$header] = $value;
            }
        }

        return $result;
    }
}