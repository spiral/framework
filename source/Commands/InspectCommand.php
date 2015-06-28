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
    protected $safetyLevels = [
        1 => '<fg=red>Very Low</fg=red>',
        2 => '<fg=red>Bad</fg=red>',
        3 => '<fg=yellow>Moderate</fg=yellow>',
        4 => '<fg=yellow>Good</fg=yellow>',
        5 => '<fg=green>Very Good</fg=green>'
    ];

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = [
        ['short', 's', InputOption::VALUE_NONE, 'Return shorted report.'],
    ];

    /**
     * Get inspector instance.
     *
     * @return Inspector
     */
    protected function getInspector()
    {
        $odmBuilder = !empty(\Spiral\Commands\ODM\UpdateCommand::$schemaBuilder)
            ? \Spiral\Commands\ODM\UpdateCommand::$schemaBuilder
            : $this->odm->schemaBuilder();

        $ormBuilder = !empty(\Spiral\Commands\ORM\UpdateCommand::$schemaBuilder)
            ? \Spiral\Commands\ORM\UpdateCommand::$schemaBuilder
            : $this->orm->schemaBuilder();

        return Inspector::make([
            'schemas' => array_merge(
                $odmBuilder->getDocumentSchemas(),
                $ormBuilder->getRecordSchemas()
            )
        ]);
    }

    /**
     * Inspecting existed models.
     */
    public function perform()
    {
        $inspector = $this->getInspector();
        $inspector->inspect();

        if (!$this->option('short'))
        {
            $table = $this->table([
                'Model', 'Safety Level', 'Protected', 'Warnings'
            ]);

            foreach ($inspector->getInspections() as $inspection)
            {
                $countWarnings = 0;
                foreach ($inspection->getWarnings() as $warnings)
                {
                    $countWarnings += count($warnings);
                }

                $table->addRow([
                    $inspection->getSchema()->getClass(),
                    $this->safetyLevels[$inspection->safetyLevel()],
                    $inspection->countPassed(self::MINIMAL_LEVEL) . ' / ' . $inspection->countFields(),
                    $countWarnings . " warning(s)"
                ]);
            }

            $table->render();
            $this->writeln("");
        }

        $this->writeln(interpolate(
            "Inspected models <fg=yellow>{count}</fg=yellow>, "
            . "average safety level {level} ({number}), "
            . "protected fields <info>{fields}%</info>.",
            [
                'count'  => number_format($inspector->countModels()),
                'level'  => $this->safetyLevels[(int)floor($inspector->getSafetyLevel())],
                'number' => number_format($inspector->getSafetyLevel(), 2),
                'fields' => number_format(100 * $inspector->getProtectionRate(self::MINIMAL_LEVEL), 2)
            ]
        ));

        return;
    }
}