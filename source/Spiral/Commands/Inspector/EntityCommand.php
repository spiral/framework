<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Inspector;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Describe schema and rank of one specified entity.
 */
class EntityCommand extends InspectCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'inspect:entity';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Inspect specified entity schema to locate unprotected field and rules.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['entity', InputArgument::REQUIRED, 'Entity class name.']
    ];

    /**
     * Inspecting specific models.
     */
    public function perform()
    {
        $inspector = $this->getInspector();
        $inspection = $inspector->inspection(str_replace('/', '\\', $this->argument('entity')));

        $table = $this->tableHelper([
            'Field',
            'Rank',
            'Fillable',
            'Filtered',
            'Validated',
            'Hidden'
        ]);

        foreach ($inspection->getFields() as $field) {
            $table->addRow([
                $field->getName(),
                $this->describeRank($field->getRank()),
                $field->isFillable() ? self::YES : self::GREEN_NO,
                $field->isFiltered() ? self::GREEN_YES : self::RED_NO,
                $field->isValidated() ? self::GREEN_YES : self::NO,
                $field->isHidden()
                    ? self::GREEN_YES
                    : ($field->isBlacklisted() ? self::RED_NO . ' (blacklist)' : self::NO)
            ]);
        }

        $table->render();
    }
}