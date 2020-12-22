<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Visitor\FlattenNodes;
use Spiral\Stempler\Visitor\FormatHTML;

/**
 * Prettifies HTML output.
 */
final class PrettyPrintBootloader extends Bootloader
{
    /**
     * @param StemplerBootloader $stempler
     */
    public function boot(StemplerBootloader $stempler): void
    {
        $stempler->addVisitor(FlattenNodes::class, Builder::STAGE_COMPILE);
        $stempler->addVisitor(FormatHTML::class, Builder::STAGE_COMPILE);
    }
}
