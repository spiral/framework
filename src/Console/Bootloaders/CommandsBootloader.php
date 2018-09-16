<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Bootloaders;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ModifierInterface;
use Spiral\Config\Patches\AppendPatch;
use Spiral\Core\Bootloaders\Bootloader;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
class CommandsBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @param ModifierInterface    $modifier
     * @param DirectoriesInterface $directories
     */
    public function boot(ModifierInterface $modifier, DirectoriesInterface $directories)
    {
        $this->registerDirectory($modifier, $directories, 'spiral/console/src');
        $this->registerDirectory($modifier, $directories, 'spiral/framework/src');
    }

    /**
     * @param ModifierInterface    $modifier
     * @param DirectoriesInterface $directories
     * @param string               $directory
     */
    private function registerDirectory(
        ModifierInterface $modifier,
        DirectoriesInterface $directories,
        string $directory
    ) {
        $modifier->modify(
            'tokenizer',
            new AppendPatch(
                'directories',
                null,
                $directories->get('vendor') . '/' . $directory
            )
        );
    }
}