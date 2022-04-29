<?php

declare(strict_types=1);

namespace Spiral\Files\Exception;

/**
 * When trying to read missing file.
 */
class FileNotFoundException extends FilesException
{
    public function __construct(string $filename)
    {
        parent::__construct(\sprintf('File \'%s\' not found', $filename));
    }
}
