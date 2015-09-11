<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Commands\Reactor\Prototypes;

use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Generators\Prototypes\AbstractEntity;
use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract entity generation command.
 */
class EntityCommand extends AbstractCommand
{
    /**
     * Success message. To be used by DocumentCommand.
     */
    const SUCCESS_MESSAGE = 'Entity was successfully generated:';

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * @var AbstractEntity $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        foreach ($this->option('field') as $field) {
            if (strpos($field, ':') === false) {
                throw new ReactorException("Field definition must in 'name:type' form.");
            }

            list($name, $type) = explode(':', $field);
            $generator->addField($name, $type);
        }

        //Always show
        $generator->setShowFillable(true);
        $generator->setShowHidden($this->option('hidden'));
        $generator->setShowDefaults($this->option('defaults'));
        $generator->setShowIndexes($this->option('indexes'));

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>" . static::SUCCESS_MESSAGE . "</info> {$filename}");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'field',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Schema field in a format "name:type".'
            ],
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ],
            [
                'hidden',
                'x',
                InputOption::VALUE_NONE,
                'Render hidden property.'
            ],
            [
                'defaults',
                'd',
                InputOption::VALUE_NONE,
                'Render defaults property.'
            ],
            [
                'indexes',
                'i',
                InputOption::VALUE_NONE,
                'Render indexes property.'
            ],

        ];
    }
}