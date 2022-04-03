<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * @see https://github.com/laravel/framework/tree/6.x/src/Illuminate/View/Compilers/Concerns
 */
final class ConditionalDirective extends AbstractDirective
{
    private bool $firstSwitchCase = false;

    public function renderIf(Directive $directive): string
    {
        return \sprintf('<?php if(%s): ?>', $directive->body);
    }

    public function renderUnless(Directive $directive): string
    {
        return \sprintf('<?php if(!(%s)): ?>', $directive->body);
    }

    public function renderElse(Directive $directive): string
    {
        return '<?php else: ?>';
    }

    public function renderElseif(Directive $directive): string
    {
        return \sprintf('<?php elseif(%s): ?>', $directive->body);
    }

    public function renderEndif(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    public function renderEndUnless(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    public function renderIsset(Directive $directive): string
    {
        return \sprintf('<?php if(isset(%s)): ?>', $directive->body);
    }

    public function renderEndIsset(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    public function renderEmpty(Directive $directive): string
    {
        return \sprintf('<?php if(empty(%s)): ?>', $directive->body);
    }

    public function renderEndEmpty(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    public function renderSwitch(Directive $directive): string
    {
        $this->firstSwitchCase = true;

        return \sprintf('<?php switch(%s):', $directive->body);
    }

    public function renderCase(Directive $directive): string
    {
        if ($this->firstSwitchCase) {
            $this->firstSwitchCase = false;

            return \sprintf('case (%s): ?>', $directive->body);
        }

        return \sprintf('<?php case (%s): ?>', $directive->body);
    }

    public function renderDefault(Directive $directive): string
    {
        if ($this->firstSwitchCase) {
            $this->firstSwitchCase = false;

            return 'default: ?>';
        }

        return '<?php default: ?>';
    }

    public function renderEndSwitch(Directive $directive): string
    {
        return '<?php endswitch; ?>';
    }

    public function renderBreak(Directive $directive): string
    {
        if (isset($directive->values[0])) {
            return \sprintf('<?php break %s; ?>', $directive->values[0]);
        }

        return '<?php break; ?>';
    }
}
