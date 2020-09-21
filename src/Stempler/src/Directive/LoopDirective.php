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

/**
 * Render loops and their commands.
 */
final class LoopDirective extends AbstractDirective
{
    /**
     * @param Directive $directive
     * @return string
     */
    public function renderFor(Directive $directive): string
    {
        return sprintf('<?php for(%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndfor(Directive $directive): string
    {
        return '<?php endfor; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderForeach(Directive $directive): string
    {
        return sprintf('<?php foreach(%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndforeach(Directive $directive): string
    {
        return '<?php endforeach; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderBreak(Directive $directive): string
    {
        if (isset($directive->values[0])) {
            return sprintf('<?php break %s; ?>', $directive->values[0]);
        }

        return '<?php break; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderContinue(Directive $directive): string
    {
        if (isset($directive->values[0])) {
            return sprintf('<?php continue %s; ?>', $directive->values[0]);
        }

        return '<?php continue; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    protected function renderWhile(Directive $directive): string
    {
        return sprintf('<?php while(%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    protected function renderEndwhile(Directive $directive): string
    {
        return '<?php endwhile; ?>';
    }
}
