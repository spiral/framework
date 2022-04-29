<?php

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\Bucket\ReadableInterface;
use Spiral\Storage\Bucket\UriResolvableInterface;
use Spiral\Storage\Bucket\WritableInterface;

interface BucketInterface extends
    ReadableInterface,
    WritableInterface,
    UriResolvableInterface
{
    public function file(string $pathname): FileInterface;

    public function getName(): ?string;

    public function withName(?string $name): self;
}
