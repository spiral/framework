<?php

declare(strict_types=1);

namespace Spiral\Console;

/**
 * @deprecated
 */
interface ConfirmationDefinitionInterface
{
    public function shouldBeConfirmed(): bool;
    public function getWarningMessage(): string;
}
