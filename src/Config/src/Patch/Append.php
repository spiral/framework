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

final class Append implements PatchInterface
{
    use DotTrait;

    /** @var string */
    private $position;

    /** @var null|string */
    private $key;

    /** @var mixed */
    private $value;

    /**
     * @param string      $position
     * @param null|string $key
     * @param mixed       $value
     */
    public function __construct(string $position, ?string $key, $value)
    {
        $this->position = $position === '.' ? '' : $position;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function patch(array $config): array
    {
        try {
            $target = &$this->dotGet($config, $this->position);

            if ($this->key !== null) {
                $target[$this->key] = $this->value;
            } else {
                $target[] = $this->value;
            }
        } catch (DotNotFoundException $e) {
            throw new PatchException($e->getMessage(), $e->getCode(), $e);
        }

        return $config;
    }
}
