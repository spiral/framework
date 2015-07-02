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
use Spiral\Components\ORM\SchemaBuilder;
use Spiral\Components\ORM\Schemas\RecordSchema;
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
        $this->line("class $className { ");

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

        //TODO: Complete relations
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
}