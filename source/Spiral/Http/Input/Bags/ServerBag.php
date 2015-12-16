<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Input\Bags;

/**
 * Access to server parameters of request, every requested key will be normalized for better
 * usability.
 */
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
     * {@inheritdoc}
     */
    public function has($name)
    {
        return parent::has($this->normalize($name));
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return parent::get($this->normalize($name), $default);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(array $keys, $fill = false, $filler = null)
    {
        $keys = array_map([$this, 'normalize'], $keys);

        return parent::fetch($keys, $fill, $filler);
    }
}