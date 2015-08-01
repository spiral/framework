<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Commands\InspectCommand as BaseInspectCommand;
use Spiral\Support\Models\Inspector;

class InspectCommand extends BaseInspectCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'odm:inspect';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Inspect ODM models to locate unprotected field and rules.';

    /**
     * Get inspector instance.
     *
     * @return Inspector
     */
    protected function getInspector()
    {
        if (!$schemaBuilder = SchemaCommand::$schemaBuilder)
        {
            $schemaBuilder = $this->odm->schemaBuilder();
        }

        return Inspector::make([
            'schemas' => $schemaBuilder->getDocumentSchemas()
        ]);
    }
}