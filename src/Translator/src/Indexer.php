<?php

declare(strict_types=1);

namespace Spiral\Translator;

use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Reflection\ReflectionArgument;
use Spiral\Tokenizer\Reflection\ReflectionInvocation;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Traits\TranslatorTrait;

/**
 * Index available classes and function calls to fetch every used string translation. Can
 * understand l, p and translate (trait) function.
 *
 * In addition Indexer will find every string specified in default value of model or class which
 * uses TranslatorTrait. String has to be embraced with [[ ]] in order to be indexed, you can
 * disable property indexation using @do-not-index doc comment. Translator can merge strings with
 * parent data, set class constant INHERIT_TRANSLATIONS to true.
 */
final class Indexer
{
    use LoggerTrait;

    /**
     * @param CatalogueInterface $catalogue Catalogue to aggregate messages into.
     */
    public function __construct(
        private readonly TranslatorConfig $config,
        private readonly CatalogueInterface $catalogue
    ) {
    }

    /**
     * Register string in active translator.
     */
    public function registerMessage(string $domain, string $string, bool $resolveDomain = true): void
    {
        if ($resolveDomain) {
            $domain = $this->config->resolveDomain($domain);
        }

        //Automatically registering
        if (!$this->catalogue->has($domain, $string)) {
            $this->catalogue->set($domain, $string, $string);
        }

        $this->getLogger()->debug(
            \sprintf('[%s]: `%s`', $domain, $string),
            ['domain' => $domain, 'string' => $string]
        );
    }

    /**
     * Index and register i18n string located in default properties which belongs to TranslatorTrait
     * classes.
     */
    public function indexClasses(ScopedClassesInterface $locator): void
    {
        foreach ($locator->getScopedClasses('translations', TranslatorTrait::class) as $class) {
            $strings = $this->fetchMessages($class, true);
            foreach ($strings as $string) {
                $this->registerMessage($class->getName(), $string);
            }
        }
    }

    /**
     * Index available methods and function invocations, target: l, p, $this->translate()
     * functions.
     */
    public function indexInvocations(InvocationsInterface $locator): void
    {
        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('l')
        ));

        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('p')
        ));

        $this->registerInvocations($locator->getInvocations(
            new \ReflectionMethod(TranslatorTrait::class, 'say')
        ));
    }

    /**
     * Register found invocations in translator bundles.
     *
     * @param ReflectionInvocation[] $invocations
     */
    private function registerInvocations(array $invocations): void
    {
        foreach ($invocations as $invocation) {
            if ($invocation->getArgument(0)->getType() != ReflectionArgument::STRING) {
                //We can only index invocations with constant string arguments
                continue;
            }

            $string = $invocation->getArgument(0)->stringValue();
            $string = $this->prepareMessage($string);

            $this->registerMessage($this->invocationDomain($invocation), $string, false);
        }
    }

    /**
     * Fetch default string values from class and merge it with parent strings if requested.
     */
    private function fetchMessages(\ReflectionClass $reflection, bool $inherit = false): array
    {
        $target = $reflection->getDefaultProperties() + $reflection->getConstants();

        foreach ($reflection->getProperties() as $property) {
            if (\is_string($property->getDocComment()) && \strpos($property->getDocComment(), '@do-not-index')) {
                unset($target[$property->getName()]);
            }
        }

        $strings = [];
        \array_walk_recursive($target, function ($value) use (&$strings): void {
            if (\is_string($value) && Translator::isMessage($value)) {
                $strings[] = $this->prepareMessage($value);
            }
        });

        if ($inherit && $reflection->getParentClass()) {
            //Joining strings data with parent class values (inheritance ON) - resolved into same
            //domain on export
            $strings = \array_merge(
                $strings,
                $this->fetchMessages($reflection->getParentClass(), true)
            );
        }

        return $strings;
    }

    /**
     * Get associated domain.
     */
    private function invocationDomain(ReflectionInvocation $invocation): string
    {
        //Translation using default bundle
        $domain = $this->config->getDefaultDomain();

        if ($invocation->getName() === 'say') {
            //Let's try to confirm domain
            $domain = $this->config->resolveDomain($invocation->getClass());
        }

        //`l` and `p`, `say` functions
        $argument = match (\strtolower($invocation->getName())) {
            'say', 'l' => $invocation->countArguments() >= 3 ? $invocation->getArgument(2) : null,
            'p' => $invocation->countArguments() >= 4 ? $invocation->getArgument(3) : null,
            default => null
        };

        if (!empty($argument) && $argument->getType() === ReflectionArgument::STRING) {
            //Domain specified in arguments
            $domain = $this->config->resolveDomain($argument->stringValue());
        }

        return $domain;
    }

    /**
     * Remove [[ and ]] braces from translated string.
     */
    private function prepareMessage(string $string): string
    {
        if (Translator::isMessage($string)) {
            $string = \substr($string, 2, -2);
        }

        return $string;
    }
}
