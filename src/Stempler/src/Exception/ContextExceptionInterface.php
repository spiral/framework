<?php

declare(strict_types=1);

namespace Spiral\Stempler\Exception;

use Spiral\Stempler\Parser\Context;

/**
 * Exception is able to carry template specific location.
 */
interface ContextExceptionInterface extends \Throwable
{
    public function getContext(): Context;

    public function setLocation(string $filename, int $line);
}
