<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\BootloadManager;
use Spiral\Modules\ModuleInterface;

/**
 * List all bootloaded libraries and classes.
 */
class BootloadsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:bootloads';

    /**
     * {@inheritdoc}
     */
    protected $description = 'List all bootloaded classes and libraries';

    /**
     * @param BootloadManager $bootloader
     */
    public function perform(BootloadManager $bootloader)
    {
        $grid = $this->tableHelper([
            'Class:',
            'Module:',
            'Location:'
        ]);

        foreach ($bootloader->getClasses() as $class) {
            $reflection = new \ReflectionClass($class);

            $grid->addRow([
                $reflection->getName(),
                $reflection->isSubclassOf(ModuleInterface::class) ? '<info>yes</info>' : 'no',
                $reflection->getFileName()
            ]);
        }

        $grid->render();
    }
}