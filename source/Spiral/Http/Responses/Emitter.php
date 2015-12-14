<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Responses;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * Zend's implementation echoes full stream content ignoring seeking functionality. Since original
 * emitter defines sendBody and all other methods as private i had to temporary copy this class.
 *
 * @todo PR for Zend Diactoros as SeekableEmitter ?
 */
class Emitter implements EmitterInterface
{
    /**
     * Size of chunks to send stream content by.
     */
    const STREAM_CHUNK_SIZE = 2048;

    /**
     * Minimal stream size required to switch to streaming response.
     */
    const STREAM_SIZE_THRESHOLD = 2097152;

    /**
     * Emits a response for a PHP SAPI environment.
     *
     * Emits the status line and headers via the header() function, and the
     * body content via the output buffer.
     *
     * @param ResponseInterface $response
     * @param null|int          $maxBufferLevel Maximum output buffering level to unwrap.
     */
    public function emit(ResponseInterface $response, $maxBufferLevel = null)
    {
        if (headers_sent()) {
            throw new \RuntimeException('Unable to emit response; headers already sent');
        }

        if (!$response->hasHeader('Content-Length')) {
            $response = $response->withHeader('Content-Length',
                (string)$response->getBody()->getSize());
        }

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->emitBody($response, $maxBufferLevel);
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is availble, it, too, is emitted.
     *
     * @param ResponseInterface $response
     */
    private function emitStatusLine(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));
    }

    /**
     * Emit response headers.
     *
     * Loops through each header, emitting each; if the header value
     * is an array with multiple values, ensures that each is sent
     * in such a way as to create aggregate headers (instead of replace
     * the previous).
     *
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $header => $values) {
            $name = $this->filterHeader($header);
            $first = true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);
                $first = false;
            }
        }
    }

    /**
     * Emit the message body.
     *
     * Loops through the output buffer, flushing each, before emitting
     * the response body using `echo()`.
     *
     * @param ResponseInterface $response
     * @param int               $maxBufferLevel Flush up to this buffer level.
     */
    private function emitBody(ResponseInterface $response, $maxBufferLevel)
    {
        if (null === $maxBufferLevel) {
            $maxBufferLevel = ob_get_level();
        }

        while (ob_get_level() > $maxBufferLevel) {
            ob_end_flush();
        }

        $body = $response->getBody();

        if ($body->isSeekable() && $body->getSize() > static::STREAM_SIZE_THRESHOLD) {
            //Prevents huge memory usage for big files
            while ($chunk = $body->read(static::STREAM_CHUNK_SIZE)) {
                echo $chunk;
            }
        } else {
            echo $response->getBody();
        }
    }

    /**
     * Filter a header name to wordcase
     *
     * @param string $header
     * @return string
     */
    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);

        return str_replace(' ', '-', $filtered);
    }
}