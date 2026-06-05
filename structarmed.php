<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->skipPaths([
        // per src/ has own autoload-dev which needs to be tested separately
        // if needed
        'src/*/tests',
    ])
    ->withPreset(Preset::PSR4());
