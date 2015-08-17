<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Commands\Documenters;

use Spiral\Console\Command;
use Spiral\Documenters\ODM\ODMStormDocumenter;

/**
 * Provides virtual documentation (shade classes) for PHPStorm for both
 */
class PHPStormCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'document:phpstorm';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create "virtual" documentation and ORM/ODM tooltips for PHPStorm.';

    /**
     * Perform command.
     */
    public function perform()
    {
        if ($this->container->hasBinding(\Spiral\ODM\Entities\SchemaBuilder::class)) {
            $odmBuilder = $this->container->get(\Spiral\ODM\Entities\SchemaBuilder::class);
        } else {
            $odmBuilder = $this->odm->schemaBuilder();
        }

        if ($this->container->hasBinding(\Spiral\ORM\Entities\SchemaBuilder::class)) {
            $ormBuilder = $this->container->get(\Spiral\ORM\Entities\SchemaBuilder::class);
        } else {
            $ormBuilder = $this->orm->schemaBuilder();
        }

        /**
         * @var ODMStormDocumenter $odmDocumenter
         */
        $odmDocumenter = $this->container->get(ODMStormDocumenter::class, [
            'builder' => $odmBuilder
        ]);

        $odmDocumenter->document();
        $this->writeln(
            "<info>ODM virtual documentation were created:</info> {$odmDocumenter->countClasses()} classes"
        );
    }
}