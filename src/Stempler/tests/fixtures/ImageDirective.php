<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\fixtures;

use Spiral\Stempler\Directive\AbstractDirective;
use Spiral\Stempler\Node\Dynamic\Directive;

final class ImageDirective extends AbstractDirective
{
    public function renderImage(Directive $directive): string
    {
        return \sprintf(
            '<img title=%s src=%s size=%s type=%s>',
            $directive->values[0],
            \str_starts_with($directive->values[1], '$') ? \sprintf('"<?php echo %s; ?>"', $directive->values[1]) : $directive->values[1],
            $directive->values[2],
            $directive->values[3],
        );
    }
}
