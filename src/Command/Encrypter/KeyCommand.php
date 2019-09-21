<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command\Encrypter;

use Spiral\Console\Command;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Input\InputOption;

final class KeyCommand extends Command
{
    protected const NAME        = 'encrypt:key';
    protected const DESCRIPTION = 'Generate new encryption key';

    public const OPTIONS = [
        [
            'mount',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Mount encrypter key into given file'
        ],
        [
            'placeholder',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Placeholder of encryption key (will attempt to use current encryption key)',
            '{encrypt-key}'
        ],
    ];

    /**
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->setHidden(true);
    }

    /**
     * @param EncrypterFactory $enc
     * @param FilesInterface   $files
     */
    public function perform(EncrypterFactory $enc, FilesInterface $files): void
    {
        $key = $enc->generateKey();

        $this->sprintf("<info>New encryption key:</info> <fg=cyan>%s</fg=cyan>\n", $key);

        $file = $this->option('mount');
        if ($file === null) {
            return;
        }

        if (!$files->exists($file)) {
            $this->sprintf('<error>Unable to find `%s`</error>', $file);

            return;
        }

        $content = $files->read($file);

        try {
            $content = str_replace($this->option('placeholder'), $key, $content);
            $content = str_replace($enc->getKey(), $key, $content);
        } catch (\Throwable $e) {
            // current keys is not set
        }

        $files->write($file, $content);

        $this->writeln('<comment>Encryption key has been updated.</comment>');
    }
}
