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
 * Abstract document generator (embeddable documents).
 */
class EmbeddableGenerator extends AbstractEntity
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