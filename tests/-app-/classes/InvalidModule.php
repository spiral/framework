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

class InvalidModule implements ModuleInterface
{
    public function register(RegistratorInterface $registrator)
    {
        $registrator->configure('views', 'namespaces', 'spiral/profiler', [
            "'profiler' => ", //<-----
            "   directory('libraries') . 'spiral/profiler/source/views/',",
            "   /*{{namespaces.profiler}}*/",
            "]"
        ]);
    }

    public function publish(PublisherInterface $publisher, DirectoriesInterface $directories)
    {

    }
}