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

final class ClassStmt
{
    /** @var string */
    public $name;

    /** @var string */
    public $shortName;

    /** @var string|null */
    public $alias;

    /**
     * ClassStmt constructor.
     */
    private function __construct()
    {
    }

    public function __toString(): string
    {
        if ($this->alias) {
            return "{$this->name} as $this->alias";
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
