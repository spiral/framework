<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Core\Attribute\Singleton;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Symfony\Component\Finder\Finder;

/**
 * Manages automatic container injections of class and invocation locators.
 */
#[Singleton]
final class Tokenizer
{
    /**
     * Token array constants.
     */
    public const TYPE = 0;
    public const CODE = 1;
    public const LINE = 2;

    public function __construct(
        private readonly TokenizerConfig $config
    ) {
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
     * Get pre-configured enum locator for specific scope.
     */
    public function scopedEnumLocator(string $scope): EnumsInterface
    {
        $dirs = $this->config->getScope($scope);

        return $this->enumLocator($dirs['directories'], $dirs['exclude']);
    }

    /**
     * Get pre-configured interface locator for specific scope.
     */
    public function scopedInterfaceLocator(string $scope): InterfacesInterface
    {
        $dirs = $this->config->getScope($scope);

        return $this->interfaceLocator($dirs['directories'], $dirs['exclude']);
    }

    /**
     * Get pre-configured class locator.
     */
    public function classLocator(
        array $directories = [],
        array $exclude = []
    ): ClassLocator {
        return new ClassLocator($this->makeFinder($directories, $exclude), $this->config->isDebug());
    }

    /**
     * Get pre-configured invocation locator.
     */
    public function invocationLocator(
        array $directories = [],
        array $exclude = []
    ): InvocationLocator {
        return new InvocationLocator($this->makeFinder($directories, $exclude), $this->config->isDebug());
    }

    public function enumLocator(
        array $directories = [],
        array $exclude = []
    ): EnumLocator {
        return new EnumLocator($this->makeFinder($directories, $exclude), $this->config->isDebug());
    }

    public function interfaceLocator(
        array $directories = [],
        array $exclude = []
    ): InterfaceLocator {
        return new InterfaceLocator($this->makeFinder($directories, $exclude), $this->config->isDebug());
    }

    /**
     * Get all tokes for specific file.
     */
    public static function getTokens(string $filename): array
    {
        $tokens = \token_get_all(\file_get_contents($filename));

        $line = 0;
        foreach ($tokens as &$token) {
            if (isset($token[self::LINE])) {
                $line = $token[self::LINE];
            }

            if (!\is_array($token)) {
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
