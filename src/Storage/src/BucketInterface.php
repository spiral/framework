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
    /**
     * @param string $pathname
     * @return FileInterface
     */
    public function file(string $pathname): FileInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return $this
     */
    public function withName(?string $name): self;
}
