<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Inspection configuration.
 */
class InspectionsConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'inspections';

    /**
     * @var array
     */
    protected $config = [
        'blacklist' => []
    ];

    /**
     * When such keyword met in field name inspect must assume that field should be hidden.
     *
     * @return array
     */
    public function blacklistKeywords()
    {
        return $this->config['blacklist'];
    }
}