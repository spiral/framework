<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules\Traits;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Modules\ModuleInterface;

/**
 * Converts module name into class name.
 *
 * @todo improve class name guessing
 */
trait ModuleTrait
{
    /**
     * @param string $module
     * @return string
     */
    public function guessClass($module)
    {
        $module = str_replace('/', '\\', $module);
        $module = explode('\\', $module);

        array_walk($module, function (&$chunk) {
            $chunk = Inflector::classify($chunk);
        });

        return join('\\', $module) . 'Module';
    }

    /**
     * Check if given class points to module.
     *
     * @param string $class
     * @return bool
     */
    protected function isModule($class)
    {
        if (!class_exists($class)) {
            return false;
        }

        $reflection = new \ReflectionClass($class);

        return $reflection->isSubclassOf(ModuleInterface::class);
    }
}