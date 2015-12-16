<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Core\DirectoriesInterface;
use Spiral\Encrypter\Configs\EncrypterConfig;
use Spiral\Files\FilesInterface;
use Spiral\Support\Strings;

/**
 * Updates encryption key in root/.env file. Same logic as in Laravel.
 */
class KeyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:key';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update application encryption key';

    /**
     * @param DirectoriesInterface $directories
     * @param FilesInterface       $files
     * @param EncrypterConfig      $config
     */
    public function perform(
        DirectoriesInterface $directories,
        FilesInterface $files,
        EncrypterConfig $config
    ) {
        $envFilename = $directories->directory('root') . '.env';

        if (!$files->exists($envFilename)) {
            $this->writeln(
                "<fg=red>'env.' file does not exists, unable to sek encryption key.</fg=red>"
            );
        }

        $files->write(
            $envFilename,
            str_replace($config->getKey(), Strings::random(32), $files->read($envFilename))
        );

        $this->writeln("<info>Encryption key has been successfully updated.</info>");
    }
}