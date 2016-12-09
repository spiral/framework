<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Input\Bags;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Files\Streams\StreamWrapper;

/**
 * Used to provide access to UploadedFiles property of request.
 *
 * @method UploadedFileInterface|null get(string $name, $default = null)
 * @method UploadedFileInterface[] all()
 * @method UploadedFileInterface[] fetch(array $keys, bool $fill = false, $filler = null)
 * @method \Traversable|UploadedFileInterface[] getIterator()
 */
class FilesBag extends InputBag
{
    /**
     * Get URI (virtual filename) associated with UploadedFile resource.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function uri(string $name)
    {
        if (!empty($file = $this->get($name)) && !$file->getError()) {
            return StreamWrapper::getUri($file->getStream());
        }

        return null;
    }
}