<?php

namespace Spiral\Tests\Validation\Fixtures;

use Spiral\Validation\Prototypes\AbstractChecker;

class SimpleTestChecker extends AbstractChecker
{
    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'test'   => '[[Given string should be equal to "test".]]',
        'string' => '[[Given string should be equal to another string.]]',
    ];

    /**
     * @param string $value
     * @return bool
     */
    public function test(string $value): bool
    {
        return strcasecmp($value, 'test') === 0;
    }

    /**
     * @param string $value
     * @param string $string
     * @return bool
     */
    public function string(string $value, $string): bool
    {
        return strcasecmp($value, $string) === 0;
    }
}