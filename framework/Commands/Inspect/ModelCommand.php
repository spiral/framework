<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Inspect;

use Psr\Log\LogLevel;
use Spiral\Commands\InspectCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelCommand extends InspectCommand
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'inspect:model';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Inspect specified ORM or ODM model to locate unprotected field and rules.';

    /**
     * Color levels for warnings.
     *
     * @var array
     */
    protected $warnings = array(
        LogLevel::INFO      => '{warning}',
        LogLevel::CRITICAL  => '<fg=red>{warning}</fg=red>',
        LogLevel::WARNING   => '<fg=yellow>{warning}</fg=yellow>',
        LogLevel::EMERGENCY => '<error>{warning}</error>',
    );

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['model', InputArgument::REQUIRED, 'Give detailed report for specified model.']
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['warnings', 'w', InputOption::VALUE_NONE, 'Show detailed model warnings.']
    );

    /**
     * Inspecting existed models.
     */
    public function perform()
    {
        $inspector = $this->getInspector();
        $inspector->inspect();

        $inspection = $inspector->getInspection($this->argument('model'));

        $table = $this->table(array(
            'Field', 'Safety Level', 'Fillable', 'Filtered', 'Validated', 'Hidden'
        ));

        foreach ($inspection->getFields() as $field)
        {
            $table->addRow(array(
                $field->getName(),
                $this->safetyLevels[$field->safetyLevel()],
                $field->isFillable() ? 'yes' : self::GREEN_NO,
                $field->isFiltered() ? self::GREEN_YES : 'no',
                $field->isValidated() ? self::GREEN_YES : 'no',
                $field->isHidden() ? self::GREEN_YES : ($field->isBlacklisted() ? self::RED_NO : self::NO)
            ));
        }

        $table->render();

        $protectedRate = $inspection->countPassed(self::MINIMAL_LEVEL) / $inspection->countFields();

        $this->write("\nModel safety level is " . $this->safetyLevels[$inspection->safetyLevel()] . ". ");
        $this->writeln("Fields protection rate: <info>" . number_format(100 * $protectedRate, 1) . "%</info>");

        if (!$this->option('warnings'))
        {
            //Short model info
            return;
        }

        $warnings = $inspection->getWarnings();
        if (!empty($warnings))
        {
            $table = $this->table(array("Field", "Warnings"));

            $this->writeln("\nFollowing warning were raised:");

            $countWarnings = count($warnings);
            foreach ($warnings as $field => $fieldWarnings)
            {
                $coloredWarnings = array();
                foreach ($fieldWarnings as $warning)
                {
                    $coloredWarnings[] = interpolate($this->warnings[$warning[0]], array(
                        'warning' => $warning[1]
                    ));
                }

                $table->addRow(array(
                    $field,
                    join("\n", $coloredWarnings)
                ));

                if ((bool)--$countWarnings)
                {
                    $table->addRow(new TableSeparator());
                }
            }

            $table->render();
        }
    }
}