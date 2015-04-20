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
        ['short', 's', InputOption::VALUE_OPTIONAL, 'Return short report.']
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
    }
}