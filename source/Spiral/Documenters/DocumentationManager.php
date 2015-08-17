<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Documenters;

use Spiral\Core\Singleton;

/**
 * Generates
 */
class DocumentationManager extends Singleton
{
    /**
     * Declares to Spiral IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;
}