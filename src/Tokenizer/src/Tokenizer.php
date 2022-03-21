<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Symfony\Component\Finder\Finder;

/**
 * Manages automatic container injections of class and invocation locators.
 */
final class Tokenizer implements SingletonInterface, InjectorInterface
{
    /**
     * Token array constants.
     */
    public const TYPE = 0;
    public const CODE = 1;
    public const LINE = 2;

    /** @var TokenizerConfig */
    protected $config;

    /**
     * Tokenizer constructor.
     */
    public function __construct(TokenizerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get pre-configured class locator for specific scope.
     */
    public function scopedClassLocator(string $scope): ClassesInterface
    {
        $dirs = $this->config->getScope($scope);

        return $this->classLocator($dirs['directories'], $dirs['exclude']);
    }

    /**
     * Get pre-configured class locator.
     */
    public function classLocator(
        array $directories = [],
        array $exclude = []
    ): ClassesInterface {
        return new ClassLocator($this->makeFinder($directories, $exclude));
    }

    /**
     * Get pre-configured invocation locator.
     */
    public function invocationLocator(
        array $directories = [],
        array $exclude = []
    ): InvocationsInterface {
        return new InvocationLocator($this->makeFinder($directories, $exclude));
    }

    /**
     * {@inheritdoc}
     *
     * @throws InjectionException
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        if ($class->isSubclassOf(ClassesInterface::class)) {
            return $this->classLocator();
        } elseif ($class->isSubclassOf(InvocationsInterface::class)) {
            return $this->invocationLocator();
        }

        throw new InjectionException("Unable to create injection for {$class}");
    }

    /**
     * Get all tokes for specific file.
     */
    public static function getTokens(string $filename): array
    {
        $tokens = token_get_all(file_get_contents($filename));

        $line = 0;
        foreach ($tokens as &$token) {
            if (isset($token[self::LINE])) {
                $line = $token[self::LINE];
            }

            if (!is_array($token)) {
                $token = [$token, $token, $line];
            }

            unset($token);
        }

        return $tokens;
    }

    /**
     * @param array $directories Overwrites default config values.
     * @param array $exclude     Overwrites default config values.
     */
    private function makeFinder(array $directories = [], array $exclude = []): Finder
    {
        $finder = new Finder();

        if (empty($directories)) {
            $directories = $this->config->getDirectories();
        }

        if (empty($exclude)) {
            $exclude = $this->config->getExcludes();
        }

        return $finder->files()->in($directories)->exclude($exclude)->name('*.php');
    }
}
