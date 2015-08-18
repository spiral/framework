<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Generators\RecordGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate ORM record with pre-defined schema and validation placeholders.
 */
class RecordCommand extends AbstractCommand
{
    /**
     * Success message. To be used by DocumentCommand.
     */
    const SUCCESS_MESSAGE = 'ORM Record was successfully created:';

    /**
     * Generator class to be used.
     */
    const GENERATOR = RecordGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'entity';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:record';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new ORM Record.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Record name.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * @var RecordGenerator $generator
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

        $generator->setShowFillable($this->option('fillable'));
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
                'fillable',
                's',
                 InputOption::VALUE_NONE,
                'Render fillable property.'
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