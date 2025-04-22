<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

use phpDocumentor\Reflection\Types\ClassString;
use Spiral\Prototype\Utils;

final class Type
{
    /** @var non-empty-string|null */
    public ?string $alias = null;

    /**
     * @param non-empty-string $shortName
     * @param non-empty-string|null $fullName
     */
    private function __construct(
        public readonly string $shortName,
        public readonly ?string $fullName = null,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function create(string $name): Type
    {
        $fullName = null;
        if (Utils::hasShortName($name)) {
            $fullName = $name;
            $name = Utils::shortName($name);
        }

        return new self($name, $fullName);
    }

    /**
     * @return non-empty-string
     */
    public function getAliasOrShortName(): string
    {
        return $this->alias ?: $this->shortName;
    }

    /**
     * @return non-empty-string
     */
    public function getSlashedShortName(bool $builtIn): string
    {
        $type = $this->shortName;
        if (!$builtIn && !$this->fullName) {
            $type = "\\$type";
        }

        return $type;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->fullName ?? $this->shortName;
    }
}
