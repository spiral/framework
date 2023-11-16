<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Ramsey\Uuid\UuidInterface;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\CastingErrorMessage;
use Spiral\Filters\Model\Filter;

final class CastingErrorMessages extends Filter
{
    #[Post]
    public UuidInterface $uuid;

    #[Post]
    #[CastingErrorMessage('Invalid UUID')]
    public UuidInterface $uuidWithValidationMessage;

    #[Post]
    #[CastingErrorMessage(callback: [self::class, 'validationMessageCallback'])]
    public UuidInterface $uuidWithValidationMessageCallback;

    public static function validationMessageCallback(\Throwable $e, mixed $value): string
    {
        return \sprintf('Invalid UUID: %s. Error: %s', $value, $e->getMessage());
    }
}
