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
use Spiral\Reactor\Generators\RequestGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate RequestFilter class.
 */
class RequestCommand extends AbstractCommand
{
    /**
     * Default input source.
     */
    const DEFAULT_SOURCE = 'data';

    /**
     * Default type to apply.
     */
    const DEFAULT_TYPE = 'string';

    /**
     * Generator class to be used.
     */
    const GENERATOR = RequestGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'request';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:request';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new request filter.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Request name.'],
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * @var RequestGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        foreach ($this->option('field') as $field) {
            list($field, $type, $source, $origin) = $this->parseField($field);
            $generator->addField($field, $type, $source, $origin);
        }

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>Request Filter was successfully created:</info> {$filename}");
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
                'Input field in a format "field:type(source:origin)" or "field(source)". Reactor will perform type mapping.'
            ],
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ]
        ];
    }

    /**
     * Parse field to fetch source, origin and type.
     *
     * @param string $field
     * @return array
     */
    private function parseField($field)
    {
        $source = static::DEFAULT_SOURCE;
        $type = static::DEFAULT_TYPE;
        $origin = null;

        if (strpos($field, '(') !== false) {
            $source = substr($field, strpos($field, '(') + 1, -1);
            $field = substr($field, 0, strpos($field, '('));

            if (strpos($source, ':') !== false) {
                list($source, $origin) = explode(':', $source);
            }
        }

        if (strpos($field, ':') !== false) {
            list($field, $type) = explode(':', $field);
        }

        return [$field, $type, $source, $origin];
    }
}