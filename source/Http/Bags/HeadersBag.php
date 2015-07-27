<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http\Bags;

use Spiral\Http\InputBag;

class HeadersBag extends InputBag
{
    /**
     * Parameter bag used to perform read only operations with request attributes
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Normalize header name.
     *
     * @param string $header
     * @return string
     */
    protected function normalize($header)
    {
        return str_replace(' ', '-', ucwords(str_replace('-', ' ', $header)));
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
     * @param string      $name    Property key.
     * @param mixed       $default Default value if key not exists.
     * @param bool|string $implode Implode header lines, false to return header as array.
     * @return mixed
     */
    public function get($name, $default = null, $implode = ',')
    {
        $value = parent::get($this->normalize($name), $default);

        if (!empty($implode))
        {
            return implode($implode, $value);
        }

        return $value;
    }

    /**
     * Fetch only specified keys from property values. Missed values can be filled with defined filler.
     * Attention, resulted keys will be equal to normalized names, not requested ones.
     *
     * @param array       $keys    Keys to fetch from parameter values.
     * @param bool        $fill    Fill missing key with filler value.
     * @param mixed       $filler
     * @param bool|string $implode Implode header lines, false to return header as array.
     * @return array
     */
    public function fetch(array $keys, $fill = false, $filler = null, $implode = ',')
    {
        $keys = array_map([$this, 'normalize'], $keys);

        $values = parent::fetch($keys, $fill, $filler);

        if (!empty($implode))
        {
            foreach ($values as &$value)
            {
                $value = implode($implode, $value);
                unset($value);
            }
        }

        return $values;
    }
}