<?php

declare(strict_types=1);

namespace Spiral\Storage;

interface MutableStorageInterface extends StorageInterface
{
    public function add(string $name, BucketInterface $storage): void;
}
