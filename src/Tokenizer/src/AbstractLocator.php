<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Tokenizer\Exception\LocatorException;
use Spiral\Tokenizer\Reflection\ReflectionFile;
use Symfony\Component\Finder\Finder;

/**
 * Base class for Class and Invocation locators.
 */
abstract class AbstractLocator implements InjectableInterface, LoggerAwareInterface
{
    use LoggerTrait;

    public const INJECTOR = Tokenizer::class;

    /** @var Finder */
    protected $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Available file reflections. Generator.
     *
     * @throws \Exception
     *
     * @return \Generator<int, ReflectionFile, mixed, void>
     */
    protected function availableReflections(): \Generator
    {
        foreach ($this->finder->getIterator() as $file) {
            $reflection = new ReflectionFile((string)$file);

            if ($reflection->hasIncludes()) {
                // We are not analyzing files which has includes, it's not safe to require such reflections
                $this->getLogger()->warning(
                    sprintf('File `%s` has includes and excluded from analysis', $file),
                    compact('file')
                );

                continue;
            }

            yield $reflection;
        }
    }

    /**
     * Safely get class reflection, class loading errors will be blocked and reflection will be
     * excluded from analysis.
     *
     * @template T
     * @param class-string<T> $class
     * @return \ReflectionClass<T>
     *
     * @throws LocatorException
     */
    protected function classReflection(string $class): \ReflectionClass
    {
        $loader = static function ($class) {
            if ($class === LocatorException::class) {
                return;
            }

            throw new LocatorException("Class '{$class}' can not be loaded");
        };

        //To suspend class dependency exception
        spl_autoload_register($loader);

        try {
            //In some cases reflection can thrown an exception if class invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionClass($class);
        } catch (\Throwable $e) {
            if ($e instanceof LocatorException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

            $this->getLogger()->error(
                sprintf(
                    '%s: %s in %s:%s',
                    $class,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                ['error' => $e]
            );

            throw new LocatorException($e->getMessage(), $e->getCode(), $e);
        } finally {
            spl_autoload_unregister($loader);
        }
    }

    /**
     * Get every class trait (including traits used in parents).
     *
     * @return string[]
     *
     * @psalm-return array<string, string>
     */
    protected function fetchTraits(string $class): array
    {
        $traits = [];

        do {
            $traits = \array_merge(\class_uses($class), $traits);
            $class = \get_parent_class($class);
        } while ($class !== false);

        //Traits from traits
        foreach (\array_flip($traits) as $trait) {
            $traits = \array_merge(\class_uses($trait), $traits);
        }

        return \array_unique($traits);
    }
}
