<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Translator\Loaders;

use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * Loader which allows to store translation in a form of PHP code (used for caching purposes).
 */
class PhpFileLoader extends ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(
                sprintf('File "%s" not found', $resource)
            );
        }

        return parent::load(include_once $resource, $locale, $domain);
    }
}