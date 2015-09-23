<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\ODM\DocumentEntity;
use Spiral\Reactor\Generators\Prototypes\AbstractEntity;

/**
 * Abstract document generator (embeddable documents).
 */
class DocumentEntityGenerator extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->file->addUse(DocumentEntity::class);
        $this->class->setExtends('DocumentEntity');

        parent::generate();
    }
}