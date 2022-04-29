<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode;

use Spiral\Prototype\Utils;

final class ClassStmt implements \Stringable
{
    public string $name;
    public string $shortName;
    public ?string $alias = null;

    private function __construct()
    {
    }

    public function __toString(): string
    {
        if ($this->alias) {
            return \sprintf('%s as %s', $this->name, $this->alias);
        }

        return $this->name;
    }

    public static function create(string $name, ?string $alias): ClassStmt
    {
        $stmt = new self();
        $stmt->name = $name;
        $stmt->shortName = Utils::shortName($name);
        $stmt->alias = $alias;

        return $stmt;
    }
}
