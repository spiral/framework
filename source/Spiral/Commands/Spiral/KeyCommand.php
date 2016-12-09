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
use Spiral\Encrypter\EncrypterManager;
use Spiral\Files\FilesInterface;

/**
 * Updates encryption key in root/.env file. Same logic as in Laravel.
 */
class KeyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'app:key';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Update application encryption key';

    /**
     * @param DirectoriesInterface $directories
     * @param FilesInterface       $files
     * @param EncrypterConfig      $config
     * @param EncrypterManager     $encrypterManager
     */
    public function perform(
        DirectoriesInterface $directories,
        FilesInterface $files,
        EncrypterConfig $config,
        EncrypterManager $encrypterManager
    ) {
        $envFilename = $directories->directory('root') . '.env';

        if (!$files->exists($envFilename)) {
            $this->writeln(
                "<fg=red>'env.' file does not exists, unable to sek encryption key.</fg=red>"
            );
        }

        $environmentData = $files->read($envFilename);

        $environmentData = str_replace(
            base64_encode($config->getKey()),
            base64_encode($encrypterManager->generateKey()),
            $environmentData
        );

        $files->write($envFilename, $environmentData);

        $this->writeln("<info>Encryption key has been successfully updated.</info>");
    }
}