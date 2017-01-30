<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication;

use Spiral\Core\DirectoriesInterface;
use Spiral\Modules\ModuleInterface;
use Spiral\Modules\PublisherInterface;
use Spiral\Modules\RegistratorInterface;

class WorkingModule implements ModuleInterface
{
    public function register(RegistratorInterface $registrator)
    {

    }

    public function publish(PublisherInterface $publisher, DirectoriesInterface $directories)
    {

    }
}