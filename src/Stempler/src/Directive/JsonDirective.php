<?php

declare(strict_types=1);

namespace Spiral\Stempler\Directive;

use Spiral\Stempler\Node\Dynamic\Directive;

final class JsonDirective extends AbstractDirective
{
    private const DEFAULT_OPTIONS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    public function renderJson(Directive $directive): string
    {
        return \sprintf(
            '<?php echo json_encode(%s, %s, %s) ?>',
            $directive->values[0] ?? $directive->body,
            $directive->values[1] ?? self::DEFAULT_OPTIONS,
            $directive->values[2] ?? 512
        );
    }
}
