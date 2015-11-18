<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Documenters;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Singleton;
use Spiral\Core\Traits\ConfigurableTrait;

/**
 * Only provides configuration needed for spiral documenters.
 *
 * Spiral Documenters is set of classes which used to generate set of shortcuts for selected editor,
 * at this moment only virtual classes supported (mirror of user models with clarified fields) but
 * more documenters can be added in future (for example dump schemas into JSON and feed to some
 * plugin).
 */
class Documenter extends Singleton
{
    /**
     * Configuration located in "documenters" section.
     */
    use ConfigurableTrait;

    /**
     * Declares to Spiral IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'documenters';

    /**
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->config = $configurator->getConfig(static::CONFIG);
    }
}