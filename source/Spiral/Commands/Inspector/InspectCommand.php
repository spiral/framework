<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Inspector;

use Spiral\Console\Command;
use Spiral\Models\Inspector;

/**
 * Provides inspection of ORM and ODM models to check unhidden fields, missed validations and etc.
 */
class InspectCommand extends Command
{
    /**
     * Visual constants.
     */
    const YES = 'yes';
    const NO = 'no';
    const RED_YES = '<fg=red>yes</fg=red>';
    const RED_NO = '<fg=red>no</fg=red>';
    const GREEN_YES = '<fg=green>yes</fg=green>';
    const GREEN_NO = '<fg=green>no</fg=green>';

    /**
     * Description for different rank levels, rank level multiplied by 100 in this table.
     *
     * @var array
     */
    protected $ranks = [
        0   => '<fg=red>Very Bad</fg=red>',
        25  => '<fg=red>Bad</fg=red>',
        50  => '<fg=yellow>Moderate</fg=yellow>',
        75  => '<fg=yellow>Good</fg=yellow>',
        100 => '<fg=green>Very Good</fg=green>'
    ];

    /**
     * {@inheritdoc}
     */
    protected $name = 'inspect';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Inspect ORM and ODM models to locate unprotected field and rules.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $inspector = $this->getInspector();

        if ($this->isVerbosing()) {
            $table = $this->tableHelper(['Entity', 'Rank', 'Fields', 'Fillable', 'Validated']);
            foreach ($inspector->getInspections() as $inspection) {
                $table->addRow([
                    $inspection->getName(),
                    $this->describeRank($inspection->getRank()),
                    $inspection->countFields(),
                    $inspection->countFillable(),
                    $inspection->countValidated()
                ]);
            }

            $table->render();
            $this->writeln("");
        }

        $this->writeln(interpolate(
            "Inspected entities <fg=yellow>{count}</fg=yellow>, average rank {level} ({rank}).",
            [
                'count' => number_format($inspector->countInspections()),
                'level' => $this->describeRank($inspector->getRank()),
                'rank'  => number_format($inspector->getRank(), 2)
            ]
        ));
    }

    /**
     * Get description for given rank value.
     *
     * @param float $rank
     * @return string
     */
    protected function describeRank($rank)
    {
        foreach (array_reverse($this->ranks, true) as $lowest => $message) {
            if ($rank * 100 >= $lowest) {
                return $message;
            }
        }

        return $this->ranks[0];
    }

    /**
     * Create instance of inspector associated with ORM and ODM entities.
     *
     * @return Inspector
     */
    protected function getInspector()
    {
        if ($this->container->has(\Spiral\ODM\Entities\SchemaBuilder::class)) {
            $odmBuilder = $this->container->get(\Spiral\ODM\Entities\SchemaBuilder::class);
        } else {
            $odmBuilder = $this->odm->schemaBuilder();
        }

        if ($this->container->has(\Spiral\ORM\Entities\SchemaBuilder::class)) {
            $ormBuilder = $this->container->get(\Spiral\ORM\Entities\SchemaBuilder::class);
        } else {
            $ormBuilder = $this->orm->schemaBuilder();
        }

        return $this->container->construct(Inspector::class, [
            'entities' => array_merge(
                $odmBuilder->getDocuments(),
                $ormBuilder->getRecords()
            )
        ]);
    }
}