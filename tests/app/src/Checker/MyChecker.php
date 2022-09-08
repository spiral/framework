<?php

declare(strict_types=1);

namespace Spiral\App\Checker;

use Spiral\Validation\AbstractChecker;

class MyChecker extends AbstractChecker
{
    /**
     * Error messages associated with checker method by name.
     */
    public const MESSAGES = [
        'abc' => 'Not ABC'
    ];

    public function abc(string $value): bool
    {
        return $value === 'abc';
    }
}
