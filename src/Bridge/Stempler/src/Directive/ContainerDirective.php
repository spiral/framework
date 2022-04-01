<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;

/**
 * Provides the ability to inject services into view code. Can only be used within view object.
 */
final class ContainerDirective extends AbstractDirective
{
    /**
     * Injects service into template.
     */
    public function renderInject(Directive $directive): string
    {
        if (\count($directive->values) < 2 || (string) $directive->values[0] === '') {
            throw new DirectiveException(
                'Unable to call @inject directive, 2 values required',
                $directive->getContext()
            );
        }

        if ($directive->values[0][0] === '$') {
            return \sprintf(
                '<?php %s = $this->container->get(%s); ?>',
                $directive->values[0],
                $directive->values[1]
            );
        }

        return \sprintf(
            '<?php $%s = $this->container->get(%s); ?>',
            \trim($directive->values[0], '\'"'),
            $directive->values[1]
        );
    }
}
