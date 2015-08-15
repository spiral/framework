<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
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
        $this->class->setParent('Document');

        $this->class->property('collection', [
            "Document collection, remove to make document embeddable.",
            "",
            "@var string"
        ])->setDefault(true, null);

        parent::generate();
    }
}