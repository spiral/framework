<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

/**
 * @internal Exception is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class Exception
{
    /**
     * @throws \ReflectionException
     */
    public static function withLocation(\Throwable $e, string $file, int $line): \Throwable
    {
        $fileProperty = new \ReflectionProperty($e, 'file');
        $fileProperty->setValue($e, $file);

        $lineProperty = new \ReflectionProperty($e, 'line');
        $lineProperty->setValue($e, $line);

        return $e;
    }
}
