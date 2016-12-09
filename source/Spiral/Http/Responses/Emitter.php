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
use Zend\Diactoros\Response\SapiEmitterTrait;

/**
 * Zend's implementation echoes full stream content ignoring seeking functionality. Since original
 * emitter defines sendBody and all other methods as private i had to temporary copy this class.
 */
class Emitter implements EmitterInterface
{
    use SapiEmitterTrait;

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

        $response = $this->injectContentLength($response);

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->flush($maxBufferLevel);
        $this->emitBody($response);
    }

    /**
     * Emit the message body.
     *
     * Loops through the output buffer, flushing each, before emitting the response body using
     * `echo()`.
     *
     * @param ResponseInterface $response
     */
    private function emitBody(ResponseInterface $response)
    {
        $body = $response->getBody();

        if ($body->isSeekable() && $body->getSize() > static::STREAM_SIZE_THRESHOLD) {
            $body->rewind();

            //Prevents huge memory usage for big files
            while ($chunk = $body->read(static::STREAM_CHUNK_SIZE)) {
                echo $chunk;
            }
        } else {
            echo $response->getBody();
        }
    }
}