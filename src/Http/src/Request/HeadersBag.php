<?php

declare(strict_types=1);

namespace Spiral\Http\Request;

/**
 * Provides access to headers property of server request, will normalize every requested name for
 * use convenience.
 */
final class HeadersBag extends InputBag
{
    public function has(int|string $name): bool
    {
        return parent::has($this->normalize((string) $name));
    }

    /**
     * @param false|string $implode Implode header lines, false to return header as array.
     */
    public function get(int|string $name, mixed $default = null, bool|string $implode = ','): array|string|null
    {
        $value = parent::get($this->normalize((string) $name), $default);

        if (\is_string($implode) && !empty($implode) && \is_array($value)) {
            return \implode($implode, $value);
        }

        return $value;
    }

    /**
     * @param null|string $implode Implode header lines, null to return header as array.
     */
    public function fetch(array $keys, bool $fill = false, mixed $filler = null, ?string $implode = ','): array
    {
        $keys = \array_map(fn (string $header): string => $this->normalize($header), $keys);

        $values = parent::fetch($keys, $fill, $filler);

        if (!empty($implode)) {
            foreach ($values as &$value) {
                $value = \implode($implode, $value);
                unset($value);
            }
        }

        return $values;
    }

    /**
     * Normalize header name.
     */
    protected function normalize(string $header): string
    {
        return \str_replace(
            ' ',
            '-',
            \ucwords(\str_replace('-', ' ', $header))
        );
    }
}
