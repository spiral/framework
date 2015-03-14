<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

use Spiral\Helpers\ArrayHelper;

class ParameterBag
{
    /**
     * Associated parameters to read.
     *
     * @var array
     */
    protected $data;

    /**
     * Parameter bag used to perform read only operations with request attributes.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->data = $parameters;
    }

    /**
     * Check if property key exists.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->data);
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
        if (!$this->has($name))
        {
            return $default;
        }

        return $this->data[$name];
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
     *
     * @param array $keys Keys to fetch from parameter values.
     * @param bool  $fill Fill missing key with filler value.
     * @param mixed $filler
     * @return array
     */
    public function fetch(array $keys, $fill = false, $filler = null)
    {
        $result = ArrayHelper::fetchKeys($this->data, $keys);

        if ($fill)
        {
            $result = $result + array_fill_keys($keys, $filler);
        }

        return $result;
    }
}