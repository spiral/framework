<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Request;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Streams\StreamWrapper;

/**
 * Used to provide access to UploadedFiles property of request.
 *
 * @method UploadedFileInterface|null get(string $name, $default = null)
 * @method UploadedFileInterface[] all()
 * @method UploadedFileInterface[] fetch(array $keys, bool $fill = false, $filler = null)
 * @method \Traversable|UploadedFileInterface[] getIterator()
 */
final class FilesBag extends InputBag
{
    /**
     * Locale local filename (virtual filename) associated with UploadedFile resource.
     */
    public function getFilename(string $name): ?string
    {
        if (!empty($file = $this->get($name)) && !$file->getError()) {
            return StreamWrapper::getFilename($file->getStream());
        }

        return null;
    }
}
