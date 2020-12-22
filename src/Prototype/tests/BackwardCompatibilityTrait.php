<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\Assert;

trait BackwardCompatibilityTrait
{
    /**
     * Override this method for the avoid known phpunit warning (phpunit 8.5 compatibility).
     * This method MUST be removed in spiral/prototype:^3.0 (with phpunit/phpunit: 9.0+ dependency).
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4086
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertRegExp(string $pattern, string $string, string $message = ''): void
    {
        if (\method_exists(Assert::class, 'assertMatchesRegularExpression')) {
            Assert::assertMatchesRegularExpression($pattern, $string, $message);

            return;
        }

        Assert::assertRegExp($pattern, $string, $message);
    }

    /**
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertNotRegExp(string $pattern, string $string, string $message = ''): void
    {
        if (\method_exists(Assert::class, 'assertDoesNotMatchRegularExpression')) {
            Assert::assertDoesNotMatchRegularExpression($pattern, $string, $message);

            return;
        }

        Assert::assertNotRegExp($pattern, $string, $message);
    }
}
