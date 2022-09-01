<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @group unit
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param \Traversable|array $iterable
     * @return array
     */
    protected function iterableToArray(iterable $iterable): array
    {
        if ($iterable instanceof \Traversable) {
            return \iterator_to_array($iterable, false);
        }

        return $iterable;
    }

    /**
     * @param string $class
     * @return string
     */
    protected function classNamespace(string $class): string
    {
        return \dirname(\str_replace('\\', \DIRECTORY_SEPARATOR, $class));
    }

    /**
     * @param string $class
     * @param array $fields
     * @return object
     */
    protected function newAnnotation(string $class, array $fields = []): object
    {
        $instance = new $class();

        foreach ($fields as $field => $value) {
            $instance->$field = $value;
        }

        return $instance;
    }

    /**
     * Sets an expected exception message.
     *
     * Unlike ->expectExceptionMessage(), this checks full equality, instead of
     * just a "contains" check.
     *
     * @param string $message
     *   Expected message.
     */
    public function expectExceptionMessageEquals(string $message) {
        // The "contains" check produces a more readable message.
        $this->expectExceptionMessage($message);
        // The regex check is the only way to check exact equality.
        $this->expectExceptionMessageMatches(
            '@^' . preg_quote($message, '@') . '$@'
        );
    }

    /**
     * Asserts file and line of an exception to be a position in a class file.
     *
     * @param \Throwable $e
     *   Exception or error.
     * @param string $class
     *   Class that is defined in the file.
     * @param string $search
     *   PHP fragment to identify the line.
     *
     * @throws \ReflectionException
     *   The class does not exist.
     */
    public static function assertExceptionSource(\Throwable $e, string $class, string $search) {
        $rc = new \ReflectionClass($class);
        $file = $rc->getFileName();
        self::assertSame($file, $e->getFile(), 'Exception file');
        $php = file_get_contents($file);
        $pos = strpos($php, $search);
        self::assertIsInt($pos);
        $line = substr_count($php, "\n", 0, $pos) + 1;
        self::assertSame($line, $e->getLine(), 'Exception line number');
    }
}
