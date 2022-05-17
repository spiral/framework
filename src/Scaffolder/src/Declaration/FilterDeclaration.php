<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Filters\Filter;
use Spiral\Scaffolder\Config\ScaffolderConfig;

class FilterDeclaration extends AbstractDeclaration
{
    public const TYPE = 'filter';

    /**
     * Default input source.
     */
    private const DEFAULT_SOURCE = 'data';

    public function __construct(
        ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        private readonly array $mapping = [],
    ) {
        parent::__construct($config, $name, $comment);
    }

    /**
     * Add new field to request and generate default filters and validations if type presented in mapping.
     */
    public function declareField(string $field, ?string $type, ?string $source, ?string $origin = null): void
    {
        $schema = $this->class->getConstant('SCHEMA')->getValue();
        $validates = $this->class->getConstant('VALIDATES')->getValue();

        if (!isset($this->mapping[$type])) {
            $schema[$field] = ($source ?? self::DEFAULT_SOURCE) . ':' . ($origin ?: $field);

            $this->class->getConstant('SCHEMA')->setValue($schema);

            return;
        }

        $definition = $this->mapping[$type];

        //Source can depend on type
        $source ??= $definition['source'];
        $schema[$field] = $source . ':' . ($origin ?: $field);

        if (!empty($definition['validates'])) {
            //Pre-defined validation
            $validates[$field] = $definition['validates'];
        }

        $this->class->getConstant('SCHEMA')->setValue($schema);
        $this->class->getConstant('VALIDATES')->setValue($validates);
    }

    public function declare(): void
    {
        $this->class->setExtends(Filter::class);

        $this->class->addConstant('SCHEMA', [])->setProtected();
        $this->class->addConstant('VALIDATES', [])->setProtected();
        $this->class->addConstant('SETTERS', [])->setProtected();
    }
}
