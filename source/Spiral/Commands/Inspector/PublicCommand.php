<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Inspector;

use Spiral\Models\Inspections\FieldInspection;

/**
 * List every available entity associated with it's public fields.
 */
class PublicCommand extends InspectCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'inspect:public';

    /**
     * {@inheritdoc}
     */
    protected $description = 'List every available entity associated with it\'s public fields.';

    /**
     * Perform command.
     */
    public function perform()
    {
        $inspector = $this->getInspector();

        $table = $this->tableHelper(['Entity', 'Rank', 'Public Fields']);
        foreach ($inspector->getInspections() as $inspection) {

            $fillable = [];
            foreach ($inspection->getFields() as $field) {
                if (!$field->isHidden()) {
                    $fillable[] = $this->explainField($field);
                }
            }

            $table->addRow([
                $inspection->getName(),
                $this->describeRank($inspection->getRank()),
                empty($fillable) ? '<comment>---</comment>' : join(', ', $fillable)
            ]);
        }

        $table->render();
    }

    /**
     * Get field name with colorization according to field state.
     *
     * @param FieldInspection $field
     * @return string
     */
    private function explainField(FieldInspection $field)
    {
        if ($field->isBlacklisted()) {
            return "<fg=yellow>{$field->getName()}</fg=yellow>";
        }

        return $field->getName();
    }
}