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
 * @see https://github.com/laravel/framework/tree/6.x/src/Illuminate/View/Compilers/Concerns
 */
final class ConditionalDirective extends AbstractDirective
{
    /** @var bool */
    private $firstSwitchCase = false;

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderIf(Directive $directive): string
    {
        return sprintf('<?php if(%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderUnless(Directive $directive): string
    {
        return sprintf('<?php if(!(%s)): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderElse(Directive $directive): string
    {
        return '<?php else: ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderElseif(Directive $directive): string
    {
        return sprintf('<?php elseif(%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndif(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndUnless(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderIsset(Directive $directive): string
    {
        return sprintf('<?php if(isset(%s)): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndIsset(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEmpty(Directive $directive): string
    {
        return sprintf('<?php if(empty(%s)): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndEmpty(Directive $directive): string
    {
        return '<?php endif; ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderSwitch(Directive $directive): string
    {
        $this->firstSwitchCase = true;

        return sprintf('<?php switch(%s):', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderCase(Directive $directive): string
    {
        if ($this->firstSwitchCase) {
            $this->firstSwitchCase = false;

            return sprintf('case (%s): ?>', $directive->body);
        }

        return sprintf('<?php case (%s): ?>', $directive->body);
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderDefault(Directive $directive): string
    {
        if ($this->firstSwitchCase) {
            $this->firstSwitchCase = false;

            return 'default: ?>';
        }

        return '<?php default: ?>';
    }

    /**
     * @param Directive $directive
     * @return string
     */
    public function renderEndSwitch(Directive $directive): string
    {
        return '<?php endswitch; ?>';
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
}
