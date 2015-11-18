<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Generators\Prototypes;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ClassElements\MethodElement;
use Spiral\Reactor\PHPExpression;

/**
 * Abstract service generation.
 */
abstract class AbstractService extends AbstractGenerator
{
    /**
     * Class dependencies, will be added into boot method.
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(FilesInterface $files, $name, array $options, $header = '')
    {
        parent::__construct($files, $name, $options, $header);

        //Always first
        $this->class->method('__construct');
    }

    /**
     * Declare class as singleton.
     */
    public function makeSingleton()
    {
        $this->file->addUse(SingletonInterface::class);
        $this->class->addInterface('SingletonInterface');

        //Class pointing to self
        $this->class->setConstant(
            'SINGLETON',
            new PHPExpression('self::class'),
            ["Declares to IoC container that class must be treated as Singleton."]
        );
    }

    /**
     * Add controller method.
     *
     * @param string $method
     * @return $this
     */
    public function addMethod($method)
    {
        $this->class->method($method);

        return $this;
    }

    /**
     * Add class dependency to be added into boot method.
     *
     * @param string $name
     * @param string $dependency
     * @return $this
     */
    public function addDependency($name, $dependency)
    {
        if (strpos($name, '/') !== false || strpos($name, '\\') !== false) {
            $name = str_replace('/', '\\', $name);
            $name = substr($name, strrpos($name, '\\') + 1);
        }

        $this->dependencies[Inflector::pluralize($name)] = $dependency;
        $this->file->addUse($dependency);
    }

    /**
     * {@inheritdoc}
     */
    public function render($mode = FilesInterface::READONLY, $ensureDirectory = true)
    {
        $this->renderDependencies();

        return parent::render($mode, $ensureDirectory);
    }

    /**
     * Generate boot method and dependency properties.
     */
    protected function renderDependencies()
    {
        if (empty($this->dependencies)) {
            $this->class->removeMethod('__construct');

            return;
        }

        $construct = $this->class->method('__construct');

        //Default code and etc
        $this->initConstruct($construct);

        foreach ($this->dependencies as $name => $dependency) {
            $reflection = new \ReflectionClass($dependency);

            $this->class->property(
                $name, "@var " . $reflection->getShortName()
            )->setAccess(AbstractElement::ACCESS_PROTECTED)->setDefault(true, null);

            $construct->parameter(
                $name, $reflection->getShortName()
            )->setType($reflection->getShortName());

            $construct->setSource("\$this->$name = \$$name;", true);
        }
    }

    /**
     * Initiate default state of construct method.
     *
     * @param MethodElement $construct
     */
    protected function initConstruct(MethodElement $construct)
    {
        $this->file->addUse(ContainerInterface::class);

        $construct->parameter('container', 'ContainerInterface')->setType('ContainerInterface');
        $construct->setSource("parent::__construct(\$container);", true);
    }
}