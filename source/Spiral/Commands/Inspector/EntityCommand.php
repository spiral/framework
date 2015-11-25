<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Inspector;

use Spiral\Models\Configs\InspectionsConfig;
use Spiral\ODM\ODM;
use Spiral\ORM\ORM;
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
     * @param InspectionsConfig $config
     * @param ODM               $odm
     * @param ORM               $orm
     */
    public function perform(InspectionsConfig $config, ODM $odm, ORM $orm)
    {
        $inspector = $this->createInspector($config, $odm, $orm);

        $inspection = $inspector->inspection( $this->argument('entity'));

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