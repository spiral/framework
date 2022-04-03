<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * Render loops and their commands.
 */
final class LoopDirective extends AbstractDirective
{
    public function renderFor(Directive $directive): string
    {
        return \sprintf('<?php for(%s): ?>', $directive->body);
    }

    public function renderEndfor(Directive $directive): string
    {
        return '<?php endfor; ?>';
    }

    public function renderForeach(Directive $directive): string
    {
        return \sprintf('<?php foreach(%s): ?>', $directive->body);
    }

    public function renderEndforeach(Directive $directive): string
    {
        return '<?php endforeach; ?>';
    }

    public function renderBreak(Directive $directive): string
    {
        if (isset($directive->values[0])) {
            return \sprintf('<?php break %s; ?>', $directive->values[0]);
        }

        return '<?php break; ?>';
    }

    public function renderContinue(Directive $directive): string
    {
        if (isset($directive->values[0])) {
            return \sprintf('<?php continue %s; ?>', $directive->values[0]);
        }

        return '<?php continue; ?>';
    }

    protected function renderWhile(Directive $directive): string
    {
        return \sprintf('<?php while(%s): ?>', $directive->body);
    }

    protected function renderEndwhile(Directive $directive): string
    {
        return '<?php endwhile; ?>';
    }
}
