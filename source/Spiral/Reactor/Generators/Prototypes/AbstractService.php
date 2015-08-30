<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators\Prototypes;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\AbstractElement;
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

        //Let's always make init method first, we can always remove it
        $this->class->method('init');
    }

    /**
     * Declare class as singleton.
     */
    public function makeSingleton()
    {
        $this->file->addUse(SingletonInterface::class);
        $this->class->addInterface('SingletonInterface');

        //Class pointing to self
        $this->class->setConstant('SINGLETON', new PHPExpression('self::class'));
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
            $this->class->removeMethod('init');

            return;
        }

        $initMethod = $this->class->method('init');
        foreach ($this->dependencies as $name => $dependency) {
            $reflection = new \ReflectionClass($dependency);

            $this->class->property(
                $name, "@var " . $reflection->getShortName()
            )->setAccess(AbstractElement::ACCESS_PROTECTED)->setDefault(true, null);

            $initMethod->parameter(
                $name, $reflection->getShortName()
            )->setType($reflection->getShortName());

            $initMethod->setSource("\$this->$name = \$$name;", true);
        }
    }
}