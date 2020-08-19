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
 * Provides access to headers property of server request, will normalize every requested name for
 * use convenience.
 */
final class HeadersBag extends InputBag
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
     *
     *
     * @param bool|string $implode Implode header lines, false to return header as array.

     * @return string|array
     */
    public function get(string $name, $default = null, $implode = ',')
    {
        $value = parent::get($this->normalize($name), $default);

        if (!empty($implode) && is_array($value)) {
            return implode($implode, $value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param null|string $implode Implode header lines, null to return header as array.
     */
    public function fetch(array $keys, bool $fill = false, $filler = null, ?string $implode = ',')
    {
        $keys = array_map([$this, 'normalize'], $keys);

        $values = parent::fetch($keys, $fill, $filler);

        if (!empty($implode)) {
            foreach ($values as &$value) {
                $value = implode($implode, $value);
                unset($value);
            }
        }

        return $values;
    }

    /**
     * Normalize header name.
     *
     * @param string $header

     * @return string
     */
    protected function normalize(string $header): string
    {
        return str_replace(
            ' ',
            '-',
            ucwords(str_replace('-', ' ', $header))
        );
    }
}
