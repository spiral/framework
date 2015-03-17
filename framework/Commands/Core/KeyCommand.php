<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Core;

use Spiral\Components\Console\Command;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Generators\Config\ConfigWriter;

class KeyCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'core:key';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update environment encryption key.';

    /**
     * Updating application environment.
     */
    public function perform()
    {
        $configWriter = ConfigWriter::make(array(
            'name'   => $this->core->getEnvironment() . '/encrypter',
            'method' => ConfigWriter::MERGE_REPLACE
        ));

        //Generating key
        $key = StringHelper::random(32);

        $configWriter->setConfig(compact('key'));
        $configWriter->writeConfig();

        $this->writeln("<info>Encryption key '<comment>{$key}</comment>' "
            . "set for environment '<comment>{$this->core->getEnvironment()}</comment>'.</info>");
    }
}