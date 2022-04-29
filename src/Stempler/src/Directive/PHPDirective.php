<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

final class PHPDirective extends AbstractDirective
{
    public function renderPHP(Directive $directive): string
    {
        return '<?php';
    }

    public function renderEndPHP(Directive $directive): string
    {
        return '?>';
    }
}
