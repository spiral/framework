<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
