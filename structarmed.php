<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->skipPaths([
        'src/*/tests',
    ])
    ->withPreset(Preset::PSR4());
