<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\ODM\Document;
use Spiral\ORM\Record;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Generators\RequestGenerator;
use Spiral\Reactor\Reactor;
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
        ['name', InputArgument::REQUIRED, 'Request name.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        /**
         * @var RequestGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        if (!empty($entity = $this->option('entity'))) {
            if (empty($class = $reactor->findClass('entity', $entity))) {
                $this->writeln(
                    "<fg=red>Unable to locate entity class for '{$entity}'.</fg=red>"
                );

                return;
            }

            $generator->followEntity($this->getEntityReflection($class));
        }

        foreach ($this->option('field') as $field) {
            list($field, $type, $source, $origin) = $this->parseField($field);
            $generator->addField($field, $type, $source, $origin);
        }

        foreach ($this->option('depends') as $service) {
            if (empty($class = $reactor->findClass('service', $service))) {
                $this->writeln(
                    "<fg=red>Unable to locate service class for '{$service}'.</fg=red>"
                );

                return;
            }

            $generator->addDependency($service, $class);
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
                'entity',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Specific entity to create request for.'
            ],
            [
                'depends',
                'd',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Add request service dependency. Declare dependency in short form.'
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

    /**
     * Get entity reflection.
     *
     * @param string $entity
     * @return ReflectionEntity
     */
    private function getEntityReflection($entity)
    {
        //Getting entity type
        $reflection = new \ReflectionClass($entity);

        if ($reflection->isSubclassOf(Record::class)) {
            return $this->orm->updateSchema()->record($entity);
        }

        if ($reflection->isSubclassOf(Document::class)) {
            return $this->odm->updateSchema()->document($entity);
        }

        throw new ReactorException("Undefined entity type '{$entity}'.");
    }
}