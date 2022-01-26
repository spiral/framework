<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration\Database\Entity;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Reactor\Partial\Property;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Scaffolder\Declaration\Database\AbstractEntityDeclaration;
use Spiral\Scaffolder\Exception\ScaffolderException;

class AnnotatedDeclaration extends AbstractEntityDeclaration
{
    /**
     * {@inheritDoc}
     */
    public function addField(string $name, string $accessibility, string $type): Property
    {
        $property = parent::addField($name, $accessibility, $type);
        $this->addCommentLine($property, $this->makeFieldComment($name, $type));

        return $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return ['Cycle\Annotated\Annotation' => 'Cycle'];
    }

    public function declareSchema(): void
    {
        $entities = [];
        $attributes = ['role', 'mapper', 'repository', 'table', 'database'];
        foreach ($attributes as $attribute) {
            if (!empty($this->$attribute)) {
                $entities[] = "$attribute=\"{$this->$attribute}\"";
            }
        }

        $entity = implode(', ', $entities);
        $this->addCommentLine($this, "@Cycle\Entity($entity)");
    }

    /**
     * @psalm-suppress UndefinedDocblockClass
     *
     * @param CommentTrait $target
     */
    protected function addCommentLine($target, string $comment): void
    {
        $target->setComment(array_merge($this->getComment()->getLines(), [$comment]));
    }

    private function makeFieldComment(string $name, string $type): string
    {
        $columns = [];
        if ($this->isNullableType($type)) {
            $columns = ['nullable = true'];
        }
        $columns[] = "type = \"{$this->annotatedType($type)}\"";

        if (!empty($this->inflection)) {
            $columns = $this->addInflectedName($this->inflection, $name, $columns);
        }

        $column = implode(', ', $columns);

        return "@Cycle\Column($column)";
    }

    private function annotatedType(string $type): string
    {
        return $this->isNullableType($type) ? substr($type, 1) : $type;
    }

    private function addInflectedName(string $inflection, string $name, array $columns): array
    {
        $inflected = $this->inflect($inflection, $name);
        if ($inflected !== null && $inflected !== $name) {
            $columns[] = "name = \"$inflected\"";
        }

        return $columns;
    }

    private function inflect(string $inflection, string $value): ?string
    {
        switch ($inflection) {
            case 'tableize':
            case 't':
                return $this->tableize($value);

            case 'camelize':
            case 'c':
                return $this->camelize($value);

            default:
                throw new ScaffolderException("Unknown inflection, got `$inflection`");
        }
    }

    private function tableize(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->tableize($name);
    }

    private function camelize(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->camelize($name);
    }
}
