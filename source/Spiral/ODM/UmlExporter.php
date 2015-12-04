<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\ODM;

use Spiral\Core\Component;
use Spiral\ODM\Entities\SchemaBuilder;
use Spiral\ODM\Entities\Schemas\DocumentSchema;

/**
 * Renders ODM schema into UML (puml syntax) file.
 */
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
        'public' => '+',
        'private' => '-',
        'protected' => '#'
    ];

    /**
     * UML lines (yes we using lines to create UML).
     *
     * @var array
     */
    protected $lines = [];

    /**
     * @var SchemaBuilder
     */
    protected $builder = null;

    /**
     * @param SchemaBuilder $builder
     */
    public function __construct(SchemaBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Generate UML diagram code.
     *
     * @return bool
     */
    public function generate()
    {
        $this->line('@startuml');
        foreach ($this->builder->getDocuments() as $document) {
            $this->renderDocument($document);
        }
        $this->line('@enduml');

        return join("\n", $this->lines);
    }

    /**
     * Add document (class) to UML.
     *
     * @param DocumentSchema $document
     */
    protected function renderDocument(DocumentSchema $document)
    {
        $parent = $document->getParentDocument();
        $class = $this->normalizeName($document->getName());

        if ($document->isAbstract()) {
            $this->line("abstract class {$class} { ");
        } else {
            $this->line("class {$class} { ");
        }

        //Document fields
        foreach ($document->getFields() as $field => $type) {
            if (is_array($type)) {
                $type = $type[0] . '[]';
            }

            $field = $this->access["public"] . ' ' . addslashes($type) . ' ' . $field;
            $this->line($field, 1);
        }

        //Methods
        foreach ($document->getLocalMethods() as $method) {
            $this->renderMethod($method);
        }

        $this->line('}', 0, true);

        //Parent class relation
        if (!empty($parent)) {
            $this->line("$class --|> " . $this->normalizeName($parent->getName()), 0, true);
        }

        foreach ($document->getCompositions() as $name => $composition) {
            if (!empty($parent) && isset($parent->getCompositions()[$name])) {
                //Already declared by parent
                continue;
            }

            if ($composition['type'] == ODM::CMP_MANY) {
                $this->line(
                    "{$class} ..*" . $this->normalizeName($composition['class']) . ":{$name}",
                    0,
                    true
                );
            } else {
                $this->line(
                    "{$class} --* " . $this->normalizeName($composition['class']) . ":{$name}",
                    0,
                    true
                );
            }
        }

        foreach ($document->getAggregations() as $name => $aggregation) {
            //Show connections only for parent document
            if (!empty($parent) && isset($parent->getAggregations()[$name])) {
                continue;
            }

            if ($aggregation['type'] == Document::MANY) {
                $this->line(
                    "{$class} ..o " . $this->normalizeName($aggregation['class']) . ":{$name}",
                    0,
                    true
                );
            } else {
                $this->line(
                    "{$class} --o " . $this->normalizeName($aggregation['class']) . ":{$name}",
                    0,
                    true
                );
            }
        }
    }

    /**
     * Render document method with it's parameters and return type fetched from doc comment.
     *
     * @param \ReflectionMethod $method
     */
    protected function renderMethod(\ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = '';
            if ($parameter->isArray()) {
                $type = 'array';
            }

            if (!empty($parameter->getClass())) {
                $type = $parameter->getClass()->getShortName();
            }

            $parameters[] = ltrim($type . ' ' . $parameter->getName());
        }

        $prefix = '';
        if ($method->isAbstract()) {
            $prefix .= '{abstract} ';
        }

        if ($method->isStatic()) {
            $prefix .= '{static} ';
        }

        $accessLevel = $this->access[$this->accessLevel($method)];
        $returnValue = $this->returnValue($method);

        $this->line(
            $prefix . $accessLevel . ' ' . $returnValue . ' ' . $method->getName()
            . '(' . join(', ', $parameters) . ')',
            1
        );
    }

    /**
     * Add new line to UML diagram with specified indent.
     *
     * @param string $line
     * @param int $indent
     * @param bool $newline
     * @return $this
     */
    protected function line($line, $indent = 0, $newline = false)
    {
        $this->lines[] = str_repeat(self::indent, $indent) . $line;

        if ($newline) {
            $this->lines[] = '';
        }

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
     * Resolve method access level.
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    private function accessLevel(\ReflectionMethod $method)
    {
        if ($method->isPrivate()) {
            return 'private';
        }

        if ($method->isProtected()) {
            return 'protected';
        }

        return 'public';
    }

    /**
     * Resolve return value of given method.
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    private function returnValue(\ReflectionMethod $method)
    {
        if (preg_match('/@return\s+([^\n\s]+)/is', $method->getDocComment(), $matches)) {
            return trim($matches[1]);
        }

        return 'void';
    }
}