<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Documenters\ORM;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Cache\StoreInterface;
use Spiral\Database\Exceptions\BuilderException;
use Spiral\Database\Query\QueryResult;
use Spiral\Documenters\Documenter;
use Spiral\Documenters\Exceptions\DocumenterException;
use Spiral\Documenters\VirtualDocumenter;
use Spiral\Files\FilesInterface;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ORM\Entities\RecordIterator;
use Spiral\ORM\Entities\SchemaBuilder;
use Spiral\ORM\Entities\Schemas\RecordSchema;
use Spiral\ORM\Entities\Schemas\RelationSchema;
use Spiral\ORM\Entities\Selector;
use Spiral\ORM\Exceptions\ORMException;
use Spiral\ORM\Exceptions\SelectorException;
use Spiral\ORM\Record;
use Spiral\Pagination\Exceptions\PaginationException;
use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ClassElement;

/**
 * Generate virtual documentation for ORM classes. Works just fine in PHPStorm.
 */
class ORMStormDocumenter extends VirtualDocumenter
{
    /**
     * @var SchemaBuilder
     */
    protected $builder = null;

    /**
     * @param Documenter     $documenter
     * @param FilesInterface $files
     * @param SchemaBuilder  $builder
     */
    public function __construct(
        Documenter $documenter,
        FilesInterface $files,
        SchemaBuilder $builder
    ) {
        parent::__construct($documenter, $files);
        $this->builder = $builder;
    }

    /**
     * Generates virtual documentation based on provided schema.
     */
    public function document()
    {
        foreach ($this->builder->getRecords() as $record) {
            if ($record->isAbstract()) {
                continue;
            }

            //Render class and put it under entity name
            $this->addClass(
                $this->renderEntity($record), $record->getNamespaceName()
            );
        }

        //Let's add some uses to virtual namespace
        if (!empty($this->namespaces[$this->documenter->config()['namespace']])) {
            $namespace = $this->namespaces[$this->documenter->config()['namespace']];

            //Required uses
            $namespace->setUses([
                RecordIterator::class,
                ORMException::class,
                ServerRequestInterface::class,
                PaginationException::class,
                BuilderException::class,
                SelectorException::class,
                StoreInterface::class,
                QueryResult::class
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function renderEntity(ReflectionEntity $entity)
    {
        if (!$entity instanceof RecordSchema) {
            throw new DocumenterException("Unsupported entity type.");
        }

        $element = parent::renderEntity($entity);

        //Element Document must have defined create method
        $element->method(
            'create',
            ['@param array|\Traversable $fields', '@return ' . $entity->getShortName()],
            ['fields']
        )->setStatic(true)->parameter('fields')->setOptional(true, []);

        //Document has collection, let's clarify static methods
        $name = $entity->getName();
        $return = $entity->getShortName();

        $find = $this->helper('selector', $name)
            . '|' . $this->helper('iterator', $name)
            . '|\\' . $name . '[]';

        //Static collection methods
        $find = $element->method(
            'find',
            [
                '@param array $where',
                '@param array $load',
                '@return ' . $find
            ],
            ['where', 'load']
        )->setStatic(true);

        $find->parameter('where')->setOptional(true, [])->setType('array');
        $find->parameter('load')->setOptional(true, [])->setType('array');

        $findOne = $element->method(
            'findOne', [
            '@param array $where',
            '@param array $load',
            '@param array $sortBy',
            '@return ' . $return . '|null'
        ], ['where', 'load', 'sortBy']
        )->setStatic(true);

        $findOne->parameter('where')->setOptional(true, [])->setType('array');
        $findOne->parameter('load')->setOptional(true, [])->setType('array');
        $findOne->parameter('sortBy')->setOptional(true, [])->setType('array');

        $findByPK = $element->method(
            'findByPK', [
            '@param mixed $primaryKey',
            '@param array $load',
            '@return ' . $return . '|null'
        ], ['primaryKey']
        )->setStatic(true);

        $findByPK->parameter('load')->setOptional(true, [])->setType('array');

        //The most fun part, rendering relations!
        foreach ($entity->getRelations() as $relation) {
            if (!$relation instanceof RelationSchema) {
                //Only ORM relations for now
                continue;
            }

            if (in_array($relation->getType(), [Record::HAS_ONE, Record::BELONGS_TO])) {
                //Simple relations
                $property = $element->property($relation->getName())->setAccess(
                    AbstractElement::ACCESS_PUBLIC
                );

                $return = "\\" . $relation->getTarget();
                if ($relation->isNullable()) {
                    $return .= "|null";
                }

                $property->setComment("@var {$return}");
            }
        }

        return $element;
    }

    /**
     * @param string $name
     * @return ClassElement
     */
    protected function renderSelector($name)
    {
        $element = new ClassElement($elementName = $this->createName($name, 'selector'));
        $element->cloneSchema(Selector::class)->setComment("Virtual Selector for {$name}.");

        $element->setParent('\\' . Selector::class)->setInterfaces([]);
        $this->cleanElement($element);

        //Mounting our class
        $element->replaceComments(
            'RecordIterator',
            $this->helper('iterator', $name) . '|\\{$name}[]'
        );

        $element->replaceComments(Record::class, $name);
        $element->replaceComments("Record", '\\' . $name);
        $element->replaceComments("@return \$this", "@return \$this|{$elementName}|\\{$name}[]");

        return $element;
    }

    /**
     * @param string $name
     * @return ClassElement
     */
    protected function renderIterator($name)
    {
        $element = new ClassElement($elementName = $this->createName($name, 'iterator'));
        $element->cloneSchema(RecordIterator::class)->setComment("Virtual Iterator for {$name}.");
        $this->cleanElement($element);

        $element->setParent('\\' . RecordIterator::class)->setInterfaces([]);


        $element->replaceComments(Record::class, $name);
        $element->replaceComments("Record", '\\' . $name);
        $element->replaceComments("@return \$this", "@return \$this|{$elementName}|\\{$name}[]");

        return $element;
    }

    /**
     * Remove unnesessary element methods.
     *
     * @param ClassElement $element
     */
    private function cleanElement(ClassElement $element)
    {
        foreach ($element->getProperties() as $property) {
            $element->removeProperty($property->getName());
        }

        foreach ($element->getConstants() as $constant => $value) {
            $element->removeConstant($constant);
        }

        foreach ($element->getMethods() as $method) {
            //Remove all static, protected or magic methods
            if (
                $method->isStatic()
                || $method->getAccess() != AbstractElement::ACCESS_PUBLIC
                || substr($method->getName(), 0, 2) == '__'
            ) {
                $element->removeMethod($method->getName());
            }

            $comment = join("\n", $method->getComment());
            if (
                strpos($comment, "Record") === false
                && strpos($comment, "\$this") === false
                && $method->getName() != 'getIterator'
            ) {
                //We don't need methods not related to retrieving documents
                $element->removeMethod($method->getName());
            }
        }
    }
}