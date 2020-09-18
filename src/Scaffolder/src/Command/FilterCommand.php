<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionType;
use Spiral\Scaffolder\Declaration\FilterDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FilterCommand extends AbstractCommand
{
    protected const ELEMENT = 'filter';

    protected const NAME        = 'create:filter';
    protected const DESCRIPTION = 'Create filter declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'filter name'],
    ];
    protected const OPTIONS     = [
        [
            'entity',
            'e',
            InputOption::VALUE_OPTIONAL,
            'Source entity. Is a prior to the fields.',
        ],
        [
            'field',
            'f',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Input field in a format "field:type(source:origin)" or "field(source)".',
        ],
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    private const   NATIVE_TYPES = [
        'string', 'int', 'integer', 'float', 'double', 'bool', 'boolean', 'array',
    ];

    /**
     * Create filter declaration.
     */
    public function perform(): void
    {
        /** @var FilterDeclaration $declaration */
        $declaration = $this->createDeclaration();

        $fields = [];
        if ($this->option('entity')) {
            $name = $this->option('entity');
            try {
                $fields = $this->parseSourceEntity($name);
            } catch (ReflectionException $e) {
                $this->writeln(
                    "<fg=red>Unable to create '<comment>{$declaration->getName()} from $name</comment>' declaration: "
                    . "'<comment>{$e->getMessage()}' at {$e->getFile()}:{$e->getLine()}.</comment></fg=red>"
                );

                return;
            }
        } else {
            foreach ($this->option('field') as $field) {
                $fields[] = $this->parseField($field);
            }
        }

        foreach ($fields as $values) {
            [$field, $type, $source, $origin] = $values;

            $declaration->declareField($field, $type, $source, $origin);
        }

        $this->writeDeclaration($declaration);
    }

    /**
     * Parse field to fetch source, origin and type.
     *
     * @param string $field
     * @return array
     */
    private function parseField(string $field): array
    {
        $type = null;
        $source = null;
        $origin = null;

        if (strpos($field, '(') !== false) {
            $source = substr($field, strpos($field, '(') + 1, -1);
            $field = substr($field, 0, strpos($field, '('));

            if (strpos($source, ':') !== false) {
                [$source, $origin] = explode(':', $source);
            }
        }

        if (strpos($field, ':') !== false) {
            [$field, $type] = explode(':', $field);
        }

        return [$field, $type, $source, $origin];
    }

    /**
     * @param string $name
     * @return array
     * @throws ReflectionException
     */
    private function parseSourceEntity(string $name): array
    {
        $fields = [];
        $reflection = new ReflectionClass($name);
        foreach ($reflection->getProperties() as $property) {
            $type = $this->getTypedPropertyType($property)
                ?? $this->getPropertyTypeFromDefaults($property, $reflection)
                ?? $this->getPropertyTypeFromDocBlock($property);

            $fields[] = [$property->name, $type, null, null];
        }

        return $fields;
    }

    /**
     * @param ReflectionProperty $property
     * @return string|null
     */
    private function getTypedPropertyType(ReflectionProperty $property): ?string
    {
        if (method_exists($property, 'hasType') && method_exists($property, 'getType') && $property->hasType()) {
            /** @var ReflectionType $type */
            $type = $property->getType();
            if (method_exists($type, 'getName') && $this->isKnownType($type->getName())) {
                return $type->getName();
            }
        }

        return null;
    }

    /**
     * @param ReflectionProperty $property
     * @param ReflectionClass    $reflection
     * @return string|null
     */
    private function getPropertyTypeFromDefaults(ReflectionProperty $property, ReflectionClass $reflection): ?string
    {
        if (!isset($reflection->getDefaultProperties()[$property->name])) {
            return null;
        }

        $default = $reflection->getDefaultProperties()[$property->name];

        return $default !== null ? gettype($default) : null;
    }

    /**
     * @param ReflectionProperty $property
     * @return string|null
     */
    private function getPropertyTypeFromDocBlock(ReflectionProperty $property): ?string
    {
        $doc = $property->getDocComment();
        if (is_string($doc)) {
            preg_match('/@var\s*([\S]+)/i', $doc, $match);
            if (!empty($match[1]) && $this->isKnownType($match[1])) {
                return $match[1];
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isKnownType(string $type): bool
    {
        return in_array($type, self::NATIVE_TYPES, true);
    }
}
