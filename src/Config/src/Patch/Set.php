<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\Exception\DotNotFoundException;
use Spiral\Config\Exception\PatchException;
use Spiral\Config\Patch\Traits\DotTrait;
use Spiral\Config\PatchInterface;

/**
 * Set the value.
 */
final class Set implements PatchInterface
{
    use DotTrait;

    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /**
     * @param mixed  $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function patch(array $config): array
    {
        try {
            $target = &$this->dotGet($config, $this->key);
            $target = $this->value;
        } catch (DotNotFoundException $e) {
            throw new PatchException($e->getMessage(), $e->getCode(), $e);
        }

        return $config;
    }
}
