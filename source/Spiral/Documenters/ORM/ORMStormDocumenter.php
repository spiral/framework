<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Documenters\ORM;

use Spiral\Documenters\Documenter;
use Spiral\Documenters\Exceptions\DocumenterException;
use Spiral\Documenters\VirtualDocumenter;
use Spiral\Files\FilesInterface;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ORM\Entities\SchemaBuilder;
use Spiral\ORM\Entities\Schemas\RecordSchema;

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

//        $find = $this->helper('selector', $name)
//            . '|' . $this->helper('iterator', $name)
//            . '|\\' . $name . '[]';

        $find = $return . '[]';

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

        return $element;
    }
}