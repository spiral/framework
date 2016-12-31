<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Component;
use Spiral\Files\FilesInterface;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Http\Exceptions\ResponseException;
use Spiral\Http\Traits\JsonTrait;
use Zend\Diactoros\Stream;

/**
 * Provides ability to write content into currently active (resolved using container) response.
 *
 * @todo add more methods and wrappers
 * @see  MiddlewarePipeline
 */
class ResponseWrapper extends Component
{
    use JsonTrait;

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
     * @param UriInterface|string $uri
     * @param int                 $status
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function redirect($uri, int $status = 302): ResponseInterface
    {
        if (!is_string($uri) && !$uri instanceof UriInterface) {
            throw new \InvalidArgumentException(
                "Redirect allowed only for string or UriInterface uris"
            );
        }

        return $this->response->withStatus($status)->withHeader("Location", (string)$uri);
    }

    /**
     * Write json data into response.
     *
     * @param mixed $data
     * @param int   $code
     *
     * @return ResponseInterface
     */
    public function json($data, int $code = 200): ResponseInterface
    {
        return $this->writeJson($this->response, $data, $code);
    }

    /**
     * Configure response to send given attachment to client.
     *
     * @param string|StreamInterface|StreamableInterface $filename Local filename or stream or
     *                                                             streamable or resource.
     * @param string                                     $name     Public file name (in
     *                                                             attachment), by default local
     *                                                             filename. Name is mandratory
     *                                                             when filename supplied in a form
     *                                                             of stream or resource.
     * @param string                                     $mimetype
     *
     * @return ResponseInterface
     *
     * @throws ResponseException
     */
    public function attachment(
        $filename,
        string $name = '',
        string $mimetype = 'application/octet-stream'
    ): ResponseInterface {

        if (empty($name)) {
            if (!is_string($filename)) {
                throw new ResponseException("Unable to resolve public filename");
            }

            $name = basename($filename);
        }

        $stream = $this->getStream($filename);

        /**
         * PSR7 love to return 'self' from methods, IDE thinks now that response is MessageInterface
         *
         * @var ResponseInterface $response
         */
        $response = $this->response;

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
     *
     * @return ResponseInterface
     */
    public function html(string $body): ResponseInterface
    {
        $this->response->getBody()->write($body);

        return $this->response->withHeader('Content-type', 'text/html');
    }

    /**
     * Create stream for given filename.
     *
     * @param string|StreamInterface|StreamableInterface $filename
     *
     * @return StreamInterface
     */
    private function getStream($filename): StreamInterface
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
            throw new \InvalidArgumentException(
                "Unable to allocate response body stream, file does not exist"
            );
        }

        return new Stream(fopen($this->files->localFilename($filename), 'r'));
    }
}