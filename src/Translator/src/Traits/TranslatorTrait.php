<?php

declare(strict_types=1);

namespace Spiral\Translator\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Translator\Translator;
use Spiral\Translator\TranslatorInterface;

/**
 * Add bundle specific translation functionality, class name will be used as translation bundle.
 * In addition every default string message declared in class using [[]] braces can be indexed by
 * spiral application.
 *
 * Set constant I18N_INHERIT_MESSAGES to true to force translation indexer merge messages from class
 * and it's parents.
 */
trait TranslatorTrait
{
    /**
     * Translate message using parent class as bundle name. Method will remove string braces ([[ and
     * ]]) if specified.
     *
     * Example: $this->say("User account is invalid.");
     *
     * @param array  $options Interpolation options.
     * @param string $bundle  Translation bundle, by default current class name.
     *
     * @throws ScopeException
     */
    protected function say(string $string, array $options = [], string $bundle = null): string
    {
        if (Translator::isMessage($string)) {
            //Cut [[ and ]]
            $string = \substr($string, 2, -2);
        }

        $container = ContainerScope::getContainer();
        if (empty($container) || !$container->has(TranslatorInterface::class)) {
            return Translator::interpolate($string, $options);
        }

        /**
         * @var TranslatorInterface $translator
         */
        $translator = $container->get(TranslatorInterface::class);

        if (\is_null($bundle)) {
            $bundle = $translator->getDomain(static::class);
        }

        //Translate class string using automatically resolved message domain
        return $translator->trans($string, $options, $bundle);
    }
}
