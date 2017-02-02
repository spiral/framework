<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Traits;

use Interop\Container\ContainerInterface;
use Spiral\Core\Exceptions\ScopeException;
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
     * @param string $string
     * @param array  $options Interpolation options.
     * @param string $bundle  Translation bundle, by default current class name.
     *
     * @return string
     *
     * @throws ScopeException
     */
    protected function say(string $string, array $options = [], $bundle = null): string
    {
        if (Translator::isMessage($string)) {
            //Cut [[ and ]]
            $string = substr($string, 2, -2);
        }

        $container = $this->iocContainer();
        if (empty($container) || !$container->has(TranslatorInterface::class)) {
            throw new ScopeException("Unable to get instance of 'TranslatorInterface'");
        }

        /**
         * @var TranslatorInterface
         */
        $translator = $container->get(TranslatorInterface::class);

        if (empty($bundle)) {
            $bundle = $translator->resolveDomain(static::class);
        }

        //Translate class string using automatically resolved message domain
        return $translator->trans($string, $options, $bundle);
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function iocContainer();
}
