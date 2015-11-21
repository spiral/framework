<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)

 */
namespace Spiral\Commands\Spiral;

use Spiral\Console\Command;
use Spiral\Support\Strings;

/**
 * Update encryption key for current application enviroment.
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
    protected $description = 'Update encryption key for current environment.';

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * We are going to manipulate with encrypter configuration.
         *
         * @var ConfigWriter $write
         */
        $configWriter = $this->container->construct(ConfigWriter::class, [
            'name'   => 'encrypter',
            'method' => ConfigWriter::MERGE_REPLACE
        ]);

        $key = Strings::random(32);

        //Exporting to environment specific configuration file
        $configWriter->setConfig(compact('key'))->writeConfig(
            directory('config')  . $this->core->environment()
        );

        $this->writeln(
            "<info>Encryption key <comment>{$key}</comment> was set for environment "
            . "<comment>{$this->core->environment()}</comment>.</info>"
        );
    }
}