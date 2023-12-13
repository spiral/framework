<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Tokenizer\Exception\LocatorException;
use Spiral\Tokenizer\Reflection\ReflectionFile;
use Spiral\Tokenizer\Traits\TargetTrait;
use Symfony\Component\Finder\Finder;

/**
 * Base class for Class and Invocation locators.
 */
abstract class AbstractLocator implements InjectableInterface, LoggerAwareInterface
{
    use LoggerTrait;
    use TargetTrait;

    public const INJECTOR = Tokenizer::class;

    public function __construct(
        protected Finder $finder,
        protected readonly bool $debug = false,
    ) {
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
                if ($this->debug) {
                    $this->getLogger()->warning(
                        \sprintf('File `%s` has includes and excluded from analysis', (string) $file),
                        ['file' => $file]
                    );
                }

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

            throw new LocatorException(\sprintf("Class '%s' can not be loaded", $class));
        };

        //To suspend class dependency exception
        \spl_autoload_register($loader);

        try {
            //In some cases reflection can thrown an exception if class invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionClass($class);
        } catch (\Throwable $e) {
            if ($e instanceof LocatorException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

            if ($this->debug) {
                $this->getLogger()->error(
                    \sprintf('%s: %s in %s:%s', $class, $e->getMessage(), $e->getFile(), $e->getLine()),
                    ['error' => $e]
                );
            }

            throw new LocatorException($e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            \spl_autoload_unregister($loader);
        }
    }

    /**
     * Safely get enum reflection, class loading errors will be blocked and reflection will be
     * excluded from analysis.
     *
     * @param class-string $enum
     *
     * @throws LocatorException
     */
    protected function enumReflection(string $enum): \ReflectionEnum
    {
        $loader = static function (string $enum): void {
            if ($enum === LocatorException::class) {
                return;
            }

            throw new LocatorException(\sprintf("Enum '%s' can not be loaded", $enum));
        };

        //To suspend class dependency exception
        \spl_autoload_register($loader);

        try {
            //In some enum reflection can thrown an exception if enum invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionEnum($enum);
        } catch (\Throwable $e) {
            if ($e instanceof LocatorException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

            if ($this->debug) {
                $this->getLogger()->error(
                    \sprintf('%s: %s in %s:%s', $enum, $e->getMessage(), $e->getFile(), $e->getLine()),
                    ['error' => $e]
                );
            }

            throw new LocatorException($e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            \spl_autoload_unregister($loader);
        }
    }
}
