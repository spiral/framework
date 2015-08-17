<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Documenters\ORM;

use Spiral\Documenters\Documenter;
use Spiral\Documenters\VirtualDocumenter;
use Spiral\Files\FilesInterface;
use Spiral\ORM\Entities\SchemaBuilder;

/**
 * Generate virtual documentation for ORM classes. Works just fine in PHPStorm.
 */
class ORMStormDocumenter extends VirtualDocumenter
{
    /**
     * @var SchemaBuilder
     */
    protected $builder = null;

    /**
     * @param Documenter     $documenter
     * @param FilesInterface $files
     * @param SchemaBuilder  $builder
     */
    public function __construct(
        Documenter $documenter,
        FilesInterface $files,
        SchemaBuilder $builder
    ) {
        parent::__construct($documenter, $files);
        $this->builder = $builder;
    }

    /**
     * Generates virtual documentation based on provided schema.
     */
    public function document()
    {

    }
}