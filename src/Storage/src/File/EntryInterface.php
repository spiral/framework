<?php

declare(strict_types=1);

namespace Spiral\Storage\File;

use Spiral\Storage\BucketInterface;

interface EntryInterface extends \Stringable
{
    public function getId(): string;

    public function getPathname(): string;

    public function getBucket(): BucketInterface;
}
