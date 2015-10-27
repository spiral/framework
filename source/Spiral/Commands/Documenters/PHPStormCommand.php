<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Commands\Documenters;

use Spiral\Console\Command;
use Spiral\Documenters\Documenter;
use Spiral\Documenters\ODM\ODMStormDocumenter;
use Spiral\Documenters\ORM\ORMStormDocumenter;

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
     *
     * @param Documenter $documenter
     */
    public function perform(Documenter $documenter)
    {
        if ($this->container->has(\Spiral\ODM\Entities\SchemaBuilder::class)) {
            $odmBuilder = $this->container->get(\Spiral\ODM\Entities\SchemaBuilder::class);
        } else {
            $odmBuilder = $this->odm->schemaBuilder();
        }

        if ($this->container->has(\Spiral\ORM\Entities\SchemaBuilder::class)) {
            $ormBuilder = $this->container->get(\Spiral\ORM\Entities\SchemaBuilder::class);
        } else {
            $ormBuilder = $this->orm->schemaBuilder();
        }

        /**
         * @var ODMStormDocumenter $odmDocumenter
         */
        $odmDocumenter = $this->container->construct(ODMStormDocumenter::class, [
            'builder' => $odmBuilder
        ]);

        $odmDocumenter->document();
        $odmDocumenter->render($documenter->config()['phpstorm']['odm']);

        $this->writeln(
            "<comment>ODM virtual documentation were created:</comment> {$odmDocumenter->countClasses()} classes"
        );

        /**
         * @var ODMStormDocumenter $odmDocumenter
         */
        $ormDocumenter = $this->container->construct(ORMStormDocumenter::class, [
            'builder' => $ormBuilder
        ]);

        $ormDocumenter->document();
        $ormDocumenter->render($documenter->config()['phpstorm']['orm']);

        $this->writeln(
            "<comment>ORM virtual documentation were created:</comment> {$ormDocumenter->countClasses()} classes"
        );
    }
}