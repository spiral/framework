<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Framework;

use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;

final class ExtensionsCommand extends Command implements SingletonInterface
{
    const NAME        = 'php:ext';
    const DESCRIPTION = 'Get list of available php extension';

    /**
     * Find extensions.
     */
    public function perform()
    {
        $grid = $this->table(['Extension:', 'Version:']);
        foreach (get_loaded_extensions() as $extension) {
            $grid->addRow([$extension, phpversion($extension)]);
        }
        $grid->render();
    }
}