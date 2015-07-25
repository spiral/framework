<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ODM;

use Spiral\Components\Console\Command;
use Spiral\Components\ODM\SchemaBuilder;
use Symfony\Component\Console\Input\InputArgument;

class UpdateCommand extends Command
{
    /**
     * Schema builder instance.
     *
     * @var SchemaBuilder
     */
    public static $schemaBuilder = null;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'odm:update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ODM schema and render virtual documentation.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['documenter', InputArgument::OPTIONAL, 'IDE tooltips documenter.'],
    ];

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        benchmark('odm:updating');
        self::$schemaBuilder = $this->odm->updateSchema();
        $elapsed = benchmark('odm:updating');

        $this->writeln(
            "<info>ODM Schema has been updated " .
            "(<fg=yellow>" . number_format($elapsed, 3) . " s</fg=yellow>).</info>"
        );

        //Documentation
        $this->console->command('odm:document', $this->input, $this->output);
    }
}