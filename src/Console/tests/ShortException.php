<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Console;

class ShortException extends \Exception
{
    public function __toString()
    {
        return 'exception';
    }
}
