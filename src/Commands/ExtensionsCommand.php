<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Commands;

use Spiral\Console\Command;

final class ExtensionsCommand extends Command
{
    const NAME        = 'php:extensions';
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