<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Exporters;

use Spiral\Components\Files\FileManager;
use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\SchemaBuilder;
use Spiral\Components\ORM\Schemas\MorphedRelationSchema;
use Spiral\Components\ORM\Schemas\RecordSchema;
use Spiral\Components\ORM\Schemas\RelationSchema;
use Spiral\Core\Component;
use Spiral\Support\Generators\Reactor\BaseElement;

class UmlExporter extends Component
{
    /**
     * Indent value to use on different UML levels, 4 spaces as usual.
     */
    const indent = '  ';

    /**
     * UML labels to highlight access levels.
     *
     * @var array
     */
    protected $access = [
        BaseElement::ACCESS_PUBLIC    => '+',
        BaseElement::ACCESS_PRIVATE   => '-',
        BaseElement::ACCESS_PROTECTED => '#'
    ];

    /**
     * We are going to generate UML by lines.
     *
     * @var array
     */
    protected $lines = [];

    /**
     * ODM documents schema.
     *
     * @var SchemaBuilder
     */
    protected $builder = null;

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Function names used to render different type of relations.
     *
     * @var array
     */
    protected $relationsRenders = [
        ActiveRecord::BELONGS_TO         => 'renderBelongsTo',
        ActiveRecord::BELONGS_TO_MORPHED => 'renderBelongsToMorphed',
        ActiveRecord::HAS_ONE            => 'renderHasOne',
        ActiveRecord::HAS_MANY           => 'renderHasMany',
        ActiveRecord::MANY_TO_MANY       => 'renderManyToMany',
        ActiveRecord::MANY_TO_MORPHED    => 'renderManyToMorphed'
    ];

    /**
     * New instance of UML ODM exporter.
     *
     * @param SchemaBuilder $builder
     * @param FileManager   $file
     */
    public function __construct(SchemaBuilder $builder, FileManager $file)
    {
        $this->builder = $builder;
        $this->file = $file;
    }

    /**
     * Render UML classes diagram to specified file, all found Entities with their fields, methods
     * and relations will be used to generate such UML.
     *
     * @param string $filename
     * @return bool
     */
    public function render($filename)
    {
        $this->line('@startuml');

        foreach ($this->builder->getRecordSchemas() as $entity)
        {
            $this->renderRecord($entity);
        }

        $this->line('@enduml');

        return $this->file->write($filename, join("\n", $this->lines));
    }

    /**
     * Add new line to UML diagram with specified indent.
     *
     * @param string $line
     * @param int    $indent
     * @return $this
     */
    protected function line($line, $indent = 0)
    {
        $this->lines[] = str_repeat(self::indent, $indent) . $line;

        return $this;
    }

    /**
     * Normalize class name to show it correctly in UML data.
     *
     * @param string $class
     * @return string
     */
    protected function normalizeName($class)
    {
        return '"' . addslashes($class) . '"';
    }

    /**
     * Add entity schema to UML.
     *
     * @param RecordSchema $record
     */
    protected function renderRecord(RecordSchema $record)
    {
        $className = $this->normalizeName($record->getClass());

        if ($record->isAbstract())
        {
            $this->line("abstract class $className { ");
        }
        else
        {
            $this->line("class $className { ");
        }

        //Document fields
        foreach ($record->getFields() as $field => $type)
        {
            if (is_array($type))
            {
                $type = $type[0] . '[]';
            }

            $this->line(
                $this->access[BaseElement::ACCESS_PUBLIC] . ' ' . addslashes($type) . ' ' . $field,
                1
            );
        }

        //Methods
        foreach ($record->getMethods() as $method)
        {
            $parameters = [];
            foreach ($method->getParameters() as $parameter)
            {
                $parameters[] = ($parameter->getType() ? $parameter->getType() . ' ' : '')
                    . $parameter->getName();
            }

            $this->line(
                $this->access[$method->getAccess()] . ' '
                . $method->getReturn() . ' '
                . $method->getName() . '(' . join(', ', $parameters) . ')',
                1
            );
        }

        $this->line('}')->line('');

        //Relations
        foreach ($record->getRelations() as $relation)
        {
            $this->renderRelation($relation, $record);
        }
    }

    /**
     * Render single relation.
     *
     * @param RelationSchema $relation
     * @param RecordSchema   $parent
     */
    protected function renderRelation(RelationSchema $relation, RecordSchema $parent)
    {
        if (isset($this->relationsRenders[$relation->getType()]))
        {
            call_user_func(
                [$this, $this->relationsRenders[$relation->getType()]],
                $relation,
                $parent
            );
        }
    }

    /**
     * Render BELONGS_TO relation.
     *
     * @param RelationSchema $relation
     * @param RecordSchema   $parent
     */
    protected function renderBelongsTo(RelationSchema $relation, RecordSchema $parent)
    {
        $this->line(
            $this->normalizeName($parent->getClass())
            . " --* "
            . $this->normalizeName($relation->getTarget())
            . ":" . $relation->getName()
        );
    }

    /**
     * Render BELONGS_TO_MORPHED relation.
     *
     * @param MorphedRelationSchema $relation
     * @param RecordSchema          $parent
     */
    protected function renderBelongsToMorphed(MorphedRelationSchema $relation, RecordSchema $parent)
    {
        //Adding interface
        $this->line("interface " . $this->normalizeName($relation->getTarget()) . " {");
        $this->line("}");

        //Correcting visuals
        $this->line("hide " . $this->normalizeName($relation->getTarget()) . " methods");
        $this->line("hide " . $this->normalizeName($relation->getTarget()) . " fields");

        //Building direction
        $this->renderBelongsTo($relation, $parent);

        //Drawing all implementations
        foreach ($relation->getOuterRecordSchemas() as $record)
        {
            $this->line(
                $this->normalizeName($record->getClass())
                . " --|> "
                . $this->normalizeName($relation->getTarget())
            );
        }
    }

    /**
     * Render HAS_ONE relation.
     *
     * @param RelationSchema $relation
     * @param RecordSchema   $parent
     */
    protected function renderHasOne(RelationSchema $relation, RecordSchema $parent)
    {
        $this->line(
            $this->normalizeName($parent->getClass())
            . " --o "
            . $this->normalizeName($relation->getTarget())
            . ":" . $relation->getName()
        );
    }

    /**
     * Render HAS_MANY relation.
     *
     * @param RelationSchema $relation
     * @param RecordSchema   $parent
     */
    protected function renderHasMany(RelationSchema $relation, RecordSchema $parent)
    {
        $this->line(
            $this->normalizeName($parent->getClass())
            . " ..o "
            . $this->normalizeName($relation->getTarget())
            . ":" . $relation->getName()
        );
    }

    /**
     * Render MANY_TO_MANY relation.
     *
     * @param RelationSchema $relation
     * @param RecordSchema   $parent
     */
    protected function renderManyToMany(RelationSchema $relation, RecordSchema $parent)
    {
        //todo: implement later
    }

    /**
     * Render MANY_TO_MORPHED relation.
     *
     * @param MorphedRelationSchema $relation
     * @param RecordSchema          $parent
     */
    protected function renderManyToMorphed(MorphedRelationSchema $relation, RecordSchema $parent)
    {
        //todo: implement later
    }
}