<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Request;

/**
 * Access to server parameters of request, every requested key will be normalized for better
 * usability.
 */
final class ServerBag extends InputBag
{
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return parent::has($this->normalize($name));
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        return parent::get($this->normalize($name), $default);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(array $keys, bool $fill = false, $filler = null)
    {
        $keys = array_map([$this, 'normalize'], $keys);

        return parent::fetch($keys, $fill, $filler);
    }

    /**
     * Normalizing name to simplify selection.
     *
     *
     */
    protected function normalize(string $name): string
    {
        return preg_replace('/[^a-z\.]/i', '_', strtoupper($name));
    }
}
