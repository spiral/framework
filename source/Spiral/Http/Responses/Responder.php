<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Responses;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Http\Exceptions\ResponderException;
use Zend\Diactoros\Stream;

/**
 * Provides ability to write content into currently active (resolved using container) response.
 *
 * @todo add more methods and wrappers
 * @see  MiddlewarePipeline
 */
class Responder extends Component
{
    /**
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param ResponseInterface $response
     * @param FilesInterface    $files
     */
    public function __construct(ResponseInterface $response, FilesInterface $files)
    {
        $this->response = $response;
        $this->files = $files;
    }

    /**
     * Mount redirect headers into response
     *
     * @param UriInterface $uri
     * @param int          $status
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function redirect($uri, $status = 302)
    {
        if (!is_string($uri) && !$uri instanceof UriInterface) {
            throw new \InvalidArgumentException(
                "Redirect allowed only for string or UriInterface uris."
            );
        }

        return $this->response->withStatus($status)->withHeader("Location", (string)$uri);
    }

    /**
     * Configure response to send given attachment to client.
     *
     * @param string|StreamInterface|StreamableInterface $filename
     * @param string                                     $name Public file name (in attachment), by
     *                                                         default local filename.
     * @param string                                     $mimetype
     * @return ResponseInterface
     * @throws ResponderException
     */
    public function attachment($filename, $name = '', $mimetype = 'application/octet-stream')
    {
        $response = $this->response;
        $stream = $this->getStream($filename);

        if (empty($name)) {
            if (!is_string($filename)) {
                throw new ResponderException("Unable to resolve public filename.");
            }

            $name = basename($filename);
        }

        /**
         * PSR7 love to return 'self' from methods, IDE thinks now that response is MessageInterface
         *
         * @var ResponseInterface $response
         */
        $response = $response->withHeader('Content-Type', $mimetype);
        $response = $response->withHeader('Content-Length', (string)$stream->getSize());
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="' . addcslashes($name, '"') . '"'
        );

        return $response->withBody($stream);
    }

    /**
     * Write html content into response and set content-type header.
     *
     * @param string $body
     * @return \Psr\Http\Message\MessageInterface
     */
    public function html($body)
    {
        $this->response->getBody()->write($body);

        return $this->response->withHeader('Content-type', 'text/html');
    }

    /**
     * Create stream for given filename.
     *
     * @param string|StreamInterface|StreamableInterface $filename
     * @return StreamInterface
     */
    private function getStream($filename)
    {
        if ($filename instanceof StreamableInterface) {
            return $filename->getStream();
        }

        if ($filename instanceof StreamInterface) {
            return $filename;
        }

        if (is_resource($filename)) {
            return new Stream($filename, 'r');
        }

        if (!$this->files->isFile($filename)) {
            throw  new \InvalidArgumentException(
                "Unable to populate response body, file does not exist."
            );
        }

        return new Stream(fopen($filename, 'r'));
    }
}