<?php

declare(strict_types=1);

namespace Spiral\App\Command;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(name: 'attribute-description-constant')]
final class AttributeDescriptionConstant extends Command
{
    public const DESCRIPTION = 'Description from constant. Command configured via attribute';

    public function perform(): int
    {
        return self::SUCCESS;
    }
}
