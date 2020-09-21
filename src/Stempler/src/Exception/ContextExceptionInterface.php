<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Exception;

use Spiral\Stempler\Parser\Context;

/**
 * Exception is able to carry template specific location.
 */
interface ContextExceptionInterface extends \Throwable
{
    /**
     * @return Context
     */
    public function getContext(): Context;

    /**
     * @param string $filename
     * @param int    $line
     */
    public function setLocation(string $filename, int $line);
}
