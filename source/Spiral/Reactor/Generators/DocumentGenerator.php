<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\ODM\ActiveDocument;
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
        $this->file->addUse(ActiveDocument::class);
        $this->class->setExtends('ActiveDocument');

        parent::generate();
    }
}