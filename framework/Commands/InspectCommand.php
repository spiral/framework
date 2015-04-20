<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands;

use Spiral\Components\Console\Command;
use Spiral\Support\Models\Inspector;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InspectCommand extends Command
{
    /**
     * Just some constants.
     */
    const YES       = 'yes';
    const GREEN_YES = '<fg=green>yes</fg=green>';
    const NO        = 'no';
    const GREEN_NO  = '<fg=green>no</fg=green>';
    const RED_NO    = '<fg=red>no</fg=red>';

    /**
     * Minimal safety level used to say that field is OK.
     */
    const MINIMAL_LEVEL = 3;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'inspect';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Inspect ORM and ODM models to locate unprotected field and rules.';

    /**
     * Safety level values.
     *
     * @var array
     */
    protected $safetyLevels = array(
        1 => '<fg=red>Critical</fg=red>',
        2 => '<fg=red>Bad</fg=red>',
        3 => '<fg=yellow>Moderate</fg=yellow>',
        4 => '<fg=yellow>Good</fg=yellow>',
        5 => '<fg=green>Very Good</fg=green>'
    );

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['model', InputArgument::OPTIONAL, 'Give detailed report for specified model.']
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['short', 's', InputOption::VALUE_NONE, 'Return shorted report.'],
    );

    /**
     * Get inspector instance.
     *
     * @return Inspector
     */
    protected function getInspector()
    {
        return Inspector::make(array(
            'schemas' => array_merge(
                $this->odm->schemaBuilder()->getDocuments(),
                $this->orm->schemaBuilder()->getEntities()
            )
        ));
    }

    /**
     * Inspecting existed models.
     */
    public function perform()
    {
        $inspector = $this->getInspector();
        $inspector->inspect();

        if ($this->argument('model'))
        {
            $this->describeModel($inspector->getInspection($this->argument('model')));

            return;
        }

        if ($this->option('short'))
        {

            return;
        }
    }
}