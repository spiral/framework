<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloaders\CoreBootloader;
use Spiral\Debug\Bootloaders\DebugBootloader;
use Spiral\Tokenizer\Bootloaders\TokenizerBootloader;

abstract class Kernel extends AbstractKernel
{
    const SYSTEM = [
        CoreBootloader::class,
        DebugBootloader::class,
        TokenizerBootloader::class
    ];
}