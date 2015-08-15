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
 * Generate service class and some of it's methods. Allows to create singleton services. In future
 * more complex patterns must be implemented.
 */
class ServiceGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse('Spiral\Core\Service');
        $this->class->setParent('Service');
    }
}