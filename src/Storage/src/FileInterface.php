<?php

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\File\ReadableInterface;
use Spiral\Storage\File\UriResolvableInterface;
use Spiral\Storage\File\WritableInterface;

interface FileInterface extends
    ReadableInterface,
    WritableInterface,
    UriResolvableInterface
{
}
