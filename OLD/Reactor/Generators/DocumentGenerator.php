<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Generators;

use Spiral\ODM\Document;
use Spiral\Reactor\Generators\Prototypes\AbstractEntity;

/**
 * Document generator.
 */
class DocumentGenerator extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->file->addUse(Document::class);
        $this->class->setExtends('Document');

        parent::generate();
    }
}