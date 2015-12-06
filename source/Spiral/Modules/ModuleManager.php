<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;

/**
 * ModulesManager used to manager external spiral packages (modules) including their installation,
 * resource updates and etc.
 */
class ModuleManager extends Component implements SingletonInterface
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;
}