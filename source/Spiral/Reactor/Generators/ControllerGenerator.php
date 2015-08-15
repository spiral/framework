<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\Reactor\Generators\Prototypes\AbstractService;

/**
 * Generates controller classes.
 */
class ControllerGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse('Spiral\Core\Controller');
        $this->class->setParent('Controller');
    }
}