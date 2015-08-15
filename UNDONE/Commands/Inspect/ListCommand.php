<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Inspect;

use Spiral\Commands\InspectCommand;

class ListCommand extends InspectCommand
{
    /**
     * No magic numbers in code. :/
     */
    const UNNECESSARY_CONSTANT = 100;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'inspect:public';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Show list of models associated with their piblic fields.';

    /**
     * Fetching public fields.
     */
    public function perform()
    {
        $inspector = $this->getInspector();
        $inspector->inspect();

        $table = $this->table(['Model', 'Public Fields']);
        foreach ($inspector->getInspections() as $class => $inspection)
        {
            $publicFields = [];
            foreach ($inspection->getFields() as $field)
            {
                if (!$field->isHidden())
                {
                    $publicFields[] = $field->getName();
                }
            }

            if (empty($publicFields))
            {
                $table->addRow([
                    $class,
                    '<info>No public fields presenter.</info>'
                ]);

                continue;
            }

            $table->addRow([
                $class,
                wordwrap(join(", ", $publicFields), static::UNNECESSARY_CONSTANT)
            ]);
        }

        $table->render();
    }
}