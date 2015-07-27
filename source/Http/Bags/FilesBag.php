<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http\Bags;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Files\Streams\StreamWrapper;
use Spiral\Http\InputBag;

/**
 * Type hinting.
 *
 * @method UploadedFileInterface|null get($name, $default = null)
 * @method UploadedFileInterface[] all()
 * @method UploadedFileInterface[] fetch(array $keys, $fill = false, $filler = null)
 * @method \ArrayIterator|UploadedFileInterface[] getIterator()
 */
class FilesBag extends InputBag
{
    /**
     * Get URI (temporary "filename") associated with UploadedFile resource.
     *
     * @param string $name
     * @return null|string
     */
    public function uri($name)
    {
        if ($file = $this->get($name))
        {
            if (!$file->getError())
            {
                return StreamWrapper::getUri($file->getStream());
            }
        }

        return null;
    }
}