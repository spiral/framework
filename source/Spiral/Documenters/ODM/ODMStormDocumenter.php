<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Documenters\ODM;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Documenters\Documenter;
use Spiral\Documenters\Exceptions\DocumenterException;
use Spiral\Documenters\VirtualDocumenter;
use Spiral\Files\FilesInterface;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\Collection;
use Spiral\ODM\Entities\Compositor;
use Spiral\ODM\Entities\DocumentCursor;
use Spiral\ODM\Entities\SchemaBuilder;
use Spiral\ODM\Entities\Schemas\DocumentSchema;
use Spiral\ODM\Exceptions\DefinitionException;
use Spiral\ODM\Exceptions\ODMException;
use Spiral\ODM\ODM;
use Spiral\ODM\SimpleDocument;
use Spiral\Pagination\Exceptions\PaginationException;
use Spiral\Pagination\PaginatorInterface;
use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ClassElement;

/**
 * Generate virtual documentation for ODM classes. Works just fine in PHPStorm.
 */
class ODMStormDocumenter extends VirtualDocumenter
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
        foreach ($this->builder->getDocuments() as $document) {
            if ($document->isAbstract()) {
                continue;
            }

            //Render class and put it under entity name
            $this->addClass(
                $this->renderEntity($document), $document->getNamespaceName()
            );
        }

        //Let's add some uses to virtual namespace
        if (!empty($this->namespaces[$this->documenter->config()['namespace']])) {
            $namespace = $this->namespaces[$this->documenter->config()['namespace']];

            //Required uses
            $namespace->setUses([
                DefinitionException::class,
                ODMException::class,
                ServerRequestInterface::class,
                PaginationException::class,
                PaginatorInterface::class,
                LoggerInterface::class
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function renderEntity(ReflectionEntity $entity)
    {
        if (!$entity instanceof DocumentSchema) {
            throw new DocumenterException("Unsupported entity type.");
        }

        $element = parent::renderEntity($entity);

        //Invalid methods
        if (!empty($entity->getFields()['_id'])) {
            $element->property('_id', '\\MongoId')->setAccess(AbstractElement::ACCESS_PUBLIC);
        }

        //Element Document must have defined create method
        $element->method(
            'create',
            ['@param array|\Traversable $fields', '@return ' . $entity->getShortName()],
            ['fields']
        )->setStatic(true)->parameter('fields')->setOptional(true, []);

        //Document has collection, let's clarify static methods
        if (!$entity->isEmbeddable()) {
            $parent = $entity->getName();
            $return = $entity->getShortName();
            if ($entity->getParent(true) != $entity) {
                //Document parent (related to same collection)
                $parent = $entity->getParent(true)->getName();
                $return = $entity->getShortName() . '|\\' . $parent;
            }

            $find = $this->helper('collection', $parent)
                . '|' . $this->helper('cursor', $parent)
                . '|\\' . $parent . '[]';

            //Static collection methods
            $element->method(
                'find',
                [
                    '@param array $query',
                    '@return ' . $find
                ],
                ['query']
            )->setStatic(true)->parameter('query')->setOptional(true, [])->setType('array');

            $findOne = $element->method(
                'findOne', [
                '@param array $query',
                '@param array $sortBy',
                '@return ' . $return . '|null'
            ], ['query', 'sortBy']
            )->setStatic(true);

            $findOne->parameter('query')->setOptional(true, [])->setType('array');
            $findOne->parameter('sortBy')->setOptional(true, [])->setType('array');

            $element->method(
                'findByPK', ['@param mixed $mongoID', '@return ' . $return . '|null'], ['mongoID']
            )->setStatic(true);
        }

        //Composition (we only need to handle MANY compositions)
        foreach ($entity->getCompositions() as $name => $composition) {
            //We are going to create helper compositor class
            $class = $composition['class'];

            if ($composition['type'] == ODM::CMP_ONE) {
                $element->property($name)->setComment("@var \\" . $composition['class']);
                continue;
            }

            $element->property(
                $name, '@var ' . $this->helper('compositor', $class) . '|\\' . $class . '[]'
            );

            $element->method('get' . ucfirst($name))->setComment(
                '@return ' . $this->helper('compositor', $class) . '|\\' . $class . '[]'
            );
        }

        //Aggregations
        foreach ($entity->getAggregations() as $name => $aggregation) {
            $aggregated = $this->builder->document($aggregation['class']);
            $parent = $aggregated->getParent(true)->getName();

            if ($aggregation['type'] == Document::ONE) {
                //Simple ONE aggregation
                $element->method($name, ['@return \\' . $parent]);
            } else {
                $find = $this->helper('collection', $parent)
                    . '|' . $this->helper('cursor', $parent)
                    . '|\\' . $parent . '[]';

                $element->method(
                    $name, ['@param array $query', '@return ' . $find], ['query']
                )->parameter('query')->setOptional(true, [])->setType('array');
            }
        }

        return $element;
    }

    /**
     * @param string $name
     * @return ClassElement
     */
    protected function renderCollection($name)
    {
        $element = new ClassElement($elementName = $this->createName($name, 'collection'));
        $element->cloneSchema(Collection::class)->setComment("Virtual Collection for {$name}.");

        $element->setExtends('\\' . Collection::class)->setInterfaces([]);
        $this->cleanElement($element);

        //Mounting our class
        $element->replaceComments(
            'DocumentCursor',
            $this->helper('cursor', $name) . "|\\{$name}[]"
        );

        $this->replaceComments($element, $name, $elementName);

        return $element;
    }

    /**
     * @param string $name
     * @return ClassElement
     */
    protected function renderCursor($name)
    {
        $element = new ClassElement($elementName = $this->createName($name, 'cursor'));
        $element->cloneSchema(DocumentCursor::class)->setComment("Virtual Cursor for {$name}.");

        $element->setExtends('\\' . DocumentCursor::class)->setInterfaces([]);
        $this->cleanElement($element);

        $this->replaceComments($element, $name, $elementName);

        return $element;
    }

    /**
     * @param string $name
     * @return ClassElement
     */
    protected function renderCompositor($name)
    {
        $element = new ClassElement($elementName = $this->createName($name, 'compositor'));
        $element->cloneSchema(Compositor::class)->setComment("Virtual Compositor for {$name}.");

        $element->setExtends('\\' . Compositor::class)->setInterfaces([]);
        $this->cleanElement($element);
        $element->removeMethod('getParent');

        //Mounting our class
        $this->replaceComments($element, $name, $elementName);

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
            if (strpos($comment, "Document") === false && strpos($comment, "\$this") === false) {
                //We don't need methods not related to retrieving documents
                $element->removeMethod($method->getName());
            }
        }
    }

    /**
     * Replace document commands in rendered class.
     *
     * @param ClassElement $element
     * @param string       $name
     * @param string       $elementName
     */
    protected function replaceComments(ClassElement $element, $name, $elementName)
    {
        $element->replaceComments(SimpleDocument::class, $name);
        $element->replaceComments(Document::class, $name);
        $element->replaceComments("SimpleDocument", '\\' . $name);
        $element->replaceComments("Document", '\\' . $name);
        $element->replaceComments("@return \$this", "@return \$this|{$elementName}|\\{$name}[]");
    }
}