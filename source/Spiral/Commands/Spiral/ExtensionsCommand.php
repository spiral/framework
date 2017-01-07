<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;

class ExtensionsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'app:extensions';

    /**
     * {@inheritdoc}
     */
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