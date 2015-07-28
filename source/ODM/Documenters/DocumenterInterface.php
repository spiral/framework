<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM\Documenters;

use Spiral\Components\ODM\SchemaBuilder;
use Spiral\Core\Container;

interface DocumenterInterface
{
    /**
     * Documenters used purely while development to help IDE understand spiral code.
     *
     * @param SchemaBuilder $builder
     * @param Container     $container
     * @param array         $options
     */
    public function __construct(SchemaBuilder $builder, Container $container, array $options = []);

    /**
     * Render documentation.
     */
    public function render();
}