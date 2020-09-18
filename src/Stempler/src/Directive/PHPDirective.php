<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

final class PHPDirective extends AbstractDirective
{
    /**
     * @param Directive $directive
     * @return string
     */
    public function renderPHP(Directive $directive): string
    {
        return '<?php';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndPHP(Directive $directive): string
    {
        return '?>';
    }
}
