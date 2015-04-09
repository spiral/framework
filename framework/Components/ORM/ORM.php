<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Core\Component;
use Spiral\Core\Core;

class ORM extends Component
{
    /**
     * Required traits.
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'orm';

    /**
     * ORM component instance.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
        $this->config = $core->loadConfig('orm');
    }

    /**
     * Get ORM schema reader. Schema will detect all declared entities, their tables, columns,
     * relationships and etc.
     *
     * @return SchemaReader
     */
    public function schemaReader()
    {
        return SchemaReader::make(array(
            'config' => $this->config
        ));
    }
}