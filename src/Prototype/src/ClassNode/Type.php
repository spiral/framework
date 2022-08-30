<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

use Spiral\Prototype\Utils;

final class Type
{
    public ?string $alias = null;

    private function __construct(
        public readonly string $shortName,
        public readonly ?string $fullName = null,
    ) {
    }

    public static function create(string $name): Type
    {
        $fullName = null;
        if (Utils::hasShortName($name)) {
            $fullName = $name;
            $name = Utils::shortName($name);
        }

        return new self($name, $fullName);
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
}
