<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Documenters\ODM;

use Spiral\Documenters\DocumenterException;
use Spiral\Documenters\VirtualDocumenter;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ODM\Entities\SchemaBuilder;
use Spiral\ODM\Entities\Schemas\DocumentSchema;

/**
 * Generate virtual documentation for ODM classes. Works just fine in PHPStorm.
 */
class ODMStormDocumenter extends VirtualDocumenter
{
    /**
     * Generates virtual documentation based on provided schema.
     *
     * @param SchemaBuilder $builder
     */
    public function generate(SchemaBuilder $builder)
    {
        foreach ($builder->getDocuments() as $document) {
            if ($document->isAbstract()) {
                continue;
            }

            //Render class and put it under entity name
            $this->addClass(
                $this->renderEntity($document),
                $document->getNamespaceName()
            );
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
            $element->property('_id', '\\MongoId');
        }

        return $element;
    }
}