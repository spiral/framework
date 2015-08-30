<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\ORM\Record;
use Spiral\Reactor\Generators\Prototypes\AbstractEntity;

/**
 * ORM record generator.
 */
class RecordGenerator extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->file->addUse(Record::class);
        $this->class->setExtends('Record');

        parent::generate();
    }
}