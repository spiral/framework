<?php

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
    protected const OPTIONS     = [
        [
            'mount',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Mount encrypter key into given file',
        ],
        [
            'placeholder',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Placeholder of encryption key (will attempt to use current encryption key)',
            '{encrypt-key}',
        ],
    ];

    public function perform(EncrypterFactory $enc, FilesInterface $files): int
    {
        $key = $enc->generateKey();

        $this->sprintf("<info>New encryption key:</info> <fg=cyan>%s</fg=cyan>\n", $key);

        $file = $this->option('mount');
        if ($file === null) {
            return self::SUCCESS;
        }

        if (!$files->exists($file)) {
            $this->sprintf('<error>Unable to find `%s`</error>', $file);
            return self::FAILURE;
        }

        $content = $files->read($file);

        try {
            $content = \str_replace($this->option('placeholder'), $key, $content);
            $content = \str_replace($enc->getKey(), $key, $content);
        } catch (\Throwable) {
            // current keys is not set
        }

        $files->write($file, $content);

        $this->writeln('<comment>Encryption key has been updated.</comment>');

        return self::SUCCESS;
    }
}
