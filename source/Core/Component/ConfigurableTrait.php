<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Component;

trait ConfigurableTrait
{
    /**
     * Component configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Current component configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Update config with new values, new configuration will be merged with old one.
     *
     * @param array $config
     * @return array
     */
    public function setConfig(array $config)
    {
        return $this->config = $config + $this->config;
    }
}