<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Streams;

use Psr\Http\Message\StreamInterface;

/**
 * Class contain PSR-7 compatible body.
 */
interface StreamableInterface
{
    /**
     * @return StreamInterface
     */
    public function getStream(): StreamInterface;
}
