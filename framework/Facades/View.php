<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager as ViewComponent;
use Spiral\Core\Events\DispatcherInterface;
use Spiral\Core\Facade;

//todo: new list of functions
class View extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'view';
}