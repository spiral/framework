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

final class KeyCommand extends Command
{
    protected const NAME        = "encrypt:key";
    protected const DESCRIPTION = "Generate new encryption key";

    /**
     * @param null|string $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->setHidden(true);
    }

    /**
     * @param EncrypterFactory $enc
     */
    public function perform(EncrypterFactory $enc)
    {
        $this->writeln($enc->generateKey());
    }
}