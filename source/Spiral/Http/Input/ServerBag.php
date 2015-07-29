<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http\Input;

class ServerBag extends InputBag
{
    /**
     * Normalizing name to simplify selection.
     *
     * @param string $name
     * @return string
     */
    protected function normalize($name)
    {
        return preg_replace('/[^a-z]/i', '_', strtoupper($name));
    }

    /**
     * Check if property key exists.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return parent::has($this->normalize($name));
    }

    /**
     * Get property value.
     *
     * @param string $name    Property key.
     * @param mixed  $default Default value if key not exists.
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return parent::get($this->normalize($name), $default);
    }

    /**
     * Get all property values.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Fetch only specified keys from property values. Missed values can be filled with defined filler.
     * Attention, resulted keys will be equal to normalized names, not requested ones.
     *
     * @param array $keys Keys to fetch from parameter values.
     * @param bool  $fill Fill missing key with filler value.
     * @param mixed $filler
     * @return array
     */
    public function fetch(array $keys, $fill = false, $filler = null)
    {
        $keys = array_map([$this, 'normalize'], $keys);

        return parent::fetch($keys, $fill, $filler);
    }
}