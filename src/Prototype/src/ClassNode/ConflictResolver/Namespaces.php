<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Utils;

final class Namespaces
{
    public function __construct(
        private readonly Sequences $sequences
    ) {
    }

    public function resolve(ClassNode $definition): void
    {
        $namespaces = $this->getReservedNamespaces($definition);
        $counters = $this->initiateCounters($namespaces);

        $this->resolveImportsNamespaces($definition, $counters);
    }

    private function getReservedNamespaces(ClassNode $definition): array
    {
        $namespaces = [];
        $namespaces = $this->getReservedNamespacesWithAlias($definition, $namespaces);

        return $this->getReservedNamespacesWithoutAlias($definition, $namespaces);
    }

    private function getReservedNamespacesWithAlias(ClassNode $definition, array $namespaces): array
    {
        foreach ($definition->getStmts() as $stmt) {
            if (!$stmt->alias) {
                continue;
            }

            $namespaces[$stmt->alias] = $stmt->name;
        }

        return $namespaces;
    }

    private function getReservedNamespacesWithoutAlias(ClassNode $definition, array $namespaces): array
    {
        foreach ($definition->getStmts() as $stmt) {
            if ($stmt->alias || isset($namespaces[$stmt->shortName])) {
                continue;
            }

            $namespaces[$stmt->shortName] = $stmt->name;
        }

        return $namespaces;
    }

    private function initiateCounters(array $namespaces): array
    {
        $counters = [];
        foreach ($namespaces as $shortName => $fullName) {
            $namespace = $this->parseNamespace($shortName, $fullName);

            if (isset($counters[$namespace->name])) {
                $counters[$namespace->name][$namespace->sequence] = $namespace;
            } else {
                $counters[$namespace->name] = [$namespace->sequence => $namespace];
            }
        }

        return $counters;
    }

    private function resolveImportsNamespaces(ClassNode $definition, array $counters): void
    {
        if (!$definition->hasConstructor && $definition->constructorParams) {
            foreach ($definition->constructorParams as $param) {
                //no type (or type is internal), do nothing
                if (empty($param->type) || $param->isBuiltIn()) {
                    continue;
                }

                $namespace = $this->parseNamespaceFromType($param->type);
                if (isset($counters[$namespace->name])) {
                    if ($this->getAlreadyImportedNamespace($counters[$namespace->name], $namespace)) {
                        continue;
                    }

                    $sequence = $this->sequences->find(\array_keys($counters[$namespace->name]), $namespace->sequence);
                    if ($sequence !== $namespace->sequence) {
                        $namespace->sequence = $sequence;

                        $param->type->alias = $namespace->fullName();
                    }

                    $counters[$namespace->name][$sequence] = $namespace;
                } else {
                    $counters[$namespace->name] = [$namespace->sequence => $namespace];
                }
            }
        }

        foreach ($definition->dependencies as $dependency) {
            $namespace = $this->parseNamespaceFromType($dependency->type);
            if (isset($counters[$namespace->name])) {
                $alreadyImported = $this->getAlreadyImportedNamespace($counters[$namespace->name], $namespace);
                if ($alreadyImported !== null) {
                    $dependency->type->alias = $alreadyImported->fullName();

                    continue;
                }

                $sequence = $this->sequences->find(\array_keys($counters[$namespace->name]), $namespace->sequence);
                if ($sequence !== $namespace->sequence) {
                    $namespace->sequence = $sequence;

                    $dependency->type->alias = $namespace->fullName();
                }

                $counters[$namespace->name][$sequence] = $namespace;
            } else {
                $counters[$namespace->name] = [$namespace->sequence => $namespace];
            }
        }
    }

    /**
     * @param NamespaceEntity[] $counters
     */
    private function getAlreadyImportedNamespace(array $counters, NamespaceEntity $namespace): ?NamespaceEntity
    {
        foreach ($counters as $counter) {
            if ($counter->equals($namespace)) {
                return $counter;
            }
        }

        return null;
    }

    private function parseNamespaceFromType(ClassNode\Type $type): NamespaceEntity
    {
        return $this->parseNamespace($type->shortName, $type->name());
    }

    private function parseNamespace(string $shortName, string $fullName): NamespaceEntity
    {
        if (\preg_match("/\d+$/", $shortName, $match)) {
            $sequence = (int)$match[0];
            if ($sequence > 0) {
                return NamespaceEntity::createWithSequence(
                    Utils::trimTrailingDigits($shortName, $sequence),
                    $fullName,
                    $sequence
                );
            }
        }

        return NamespaceEntity::create($shortName, $fullName);
    }
}
