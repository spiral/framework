<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Core;

use Spiral\Console\Command;
use Spiral\Modules\ConfigWriter;
use Spiral\Support\StringHelper;

/**
 * Update encryption key for current application enviroment.
 */
class KeyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'core:key';

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
        $configWriter = $this->container->get(ConfigWriter::class, [
            'name'   => 'encrypter',
            'method' => ConfigWriter::MERGE_REPLACE
        ]);

        $key = StringHelper::random(32);

        //Exporting to environment specific configuration file
        $configWriter->setConfig(compact('key'))->writeConfig(
            directory('config') . '/' . $this->core->environment()
        );

        $this->writeln(
            "<info>Encryption key <comment>{$key}</comment> was set for environment "
            . "<comment>{$this->core->environment()}</comment>.</info>"
        );
    }
}