<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

use Spiral\Prototype\Utils;

final class Type
{
    /** @var string|null */
    public $shortName;

    /** @var string|null */
    public $alias;

    /** @var string|null */
    public $fullName;

    public static function create(string $name): Type
    {
        $type = new self();

        $fullName = null;
        if ($type->hasShortName($name)) {
            $fullName = $name;
            $name = Utils::shortName($name);
        }

        $type->shortName = $name;
        $type->fullName = $fullName;

        return $type;
    }

    public function getAliasOrShortName(): string
    {
        return $this->alias ?: $this->shortName;
    }

    public function getSlashedShortName(bool $builtIn): string
    {
        $type = $this->shortName;
        if (!$builtIn && !$this->fullName) {
            $type = "\\$type";
        }

        return $type;
    }

    public function name(): string
    {
        return $this->fullName ?? $this->shortName;
    }

    private function hasShortName(string $type): bool
    {
        return mb_strpos($type, '\\') !== false;
    }
}
