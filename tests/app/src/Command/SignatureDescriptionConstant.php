<?php

declare(strict_types=1);

namespace Spiral\App\Command;

use Spiral\Console\Command;

final class SignatureDescriptionConstant extends Command
{
    public const SIGNATURE = 'signature-description-constant';
    public const DESCRIPTION = 'Description from constant. Command configured via signature';

    public function perform(): int
    {
        return self::SUCCESS;
    }
}
