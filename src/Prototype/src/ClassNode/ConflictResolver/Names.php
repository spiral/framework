<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Utils;

final class Names
{
    public function __construct(
        private readonly Sequences $sequences
    ) {
    }

    public function resolve(ClassNode $definition): void
    {
        $reservedNames = $this->getConstructorReservedNames($definition);
        $counters = $this->initiateCounters($reservedNames);

        $this->addPostfixes($definition, $counters);
    }

    private function getConstructorReservedNames(ClassNode $definition): array
    {
        $names = \array_values($definition->constructorVars);
        foreach ($definition->constructorParams as $param) {
            $names[] = $param->name;
        }

        return $names;
    }

    private function initiateCounters(array $names): array
    {
        $counters = [];
        foreach ($names as $name) {
            $name = $this->parseName($name);

            if (isset($counters[$name->name])) {
                $counters[$name->name][$name->sequence] = $name->fullName();
            } else {
                $counters[$name->name] = [$name->sequence => $name->fullName()];
            }
        }

        return $counters;
    }

    private function addPostfixes(ClassNode $definition, array $counters): void
    {
        foreach ($definition->dependencies as $dependency) {
            $name = $this->parseName($dependency->var);
            if (isset($counters[$name->name])) {
                $sequence = $this->sequences->find(\array_keys($counters[$name->name]), $name->sequence);
                if ($sequence !== $name->sequence) {
                    $name->sequence = $sequence;

                    $dependency->var = $name->fullName();
                }

                $counters[$name->name][$sequence] = $name->fullName();
            } else {
                $counters[$name->name] = [$name->sequence => $name->fullName()];
            }
        }
    }

    private function parseName(string $name): NameEntity
    {
        if (\preg_match("/\d+$/", $name, $match)) {
            $sequence = (int)$match[0];
            if ($sequence > 0) {
                return NameEntity::createWithSequence(Utils::trimTrailingDigits($name, $sequence), $sequence);
            }
        }

        return NameEntity::create($name);
    }
}
