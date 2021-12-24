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

final class Prepend implements PatchInterface
{
    use DotTrait;

    /** @var string */
    private $position;

    /** @var null|string */
    private $key;

    /** @var mixed */
    private $value;

    /**
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
                $target = array_merge([$this->key => $this->value], $target);
            } else {
                array_unshift($target, $this->value);
            }
        } catch (DotNotFoundException $e) {
            throw new PatchException($e->getMessage(), $e->getCode(), $e);
        }

        return $config;
    }
}
