<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Commands\ODM\Documenters\DocumenterInterface;
use Spiral\Components\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DocumentCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'odm:document';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Re-render IDE tooltip helpers using specific Documenter.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['documenter', InputArgument::OPTIONAL, 'Specific documenter.'],
    ];

    /**
     * Update documentation.
     */
    public function perform()
    {
        //Due this is most-likely spiral application children we cal use odm configuration for
        //list of documenters
        $documenters = $this->odm->getConfig()['documenters'];

        $documenter = null;
        if (!isset($documenters[$this->argument('documenter')]))
        {
            //Default documenter
            $documenter = $documenters[$this->odm->getConfig()['documenter']];
        }

        $schemaBuilder = !empty(UpdateCommand::$schemaBuilder)
            ? UpdateCommand::$schemaBuilder
            : $this->odm->schemaBuilder();

        /**
         * @var DocumenterInterface $documenter
         */
        $documenter = $this->core->get($documenter['class'], [
            'builder' => $schemaBuilder,
            'options' => $documenter
        ]);

        //Magic happens here, special class will create shortcuts for PHPStorm IDE
        $documenter->render();

        benchmark('odm:documenter');
        $documenter->render();
        $elapsed = benchmark('odm:documenter');

        $this->writeln(
            "<info>ODM IDE tooltips documentation has been updated " .
            "(<fg=yellow>" . number_format($elapsed, 3) . " s</fg=yellow>).</info>"
        );
    }
}