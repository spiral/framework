<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ORM;

use Spiral\Commands\InspectCommand as BaseInspectCommand;
use Spiral\Support\Models\Inspector;

class InspectCommand extends BaseInspectCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'orm:inspect';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Inspect ORM models to locate unprotected field and rules.';

    /**
     * Get inspector instance.
     *
     * @return Inspector
     */
    protected function getInspector()
    {
        if (!$schemaBuilder = UpdateCommand::$schemaBuilder)
        {
            $schemaBuilder = $this->orm->schemaBuilder();
        }

        return Inspector::make(array(
            'schemas' => $schemaBuilder->getRecords()
        ));
    }
}