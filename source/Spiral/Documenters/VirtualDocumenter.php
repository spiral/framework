<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Documenters;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\Component;
use Spiral\Documenters\Exceptions\DocumenterException;
use Spiral\Files\FilesInterface;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ClassElement;
use Spiral\Reactor\FileElement;
use Spiral\Reactor\NamespaceElement;

/**
 * Provides basic needs for any spiral documenter implementation such as easier access to reactor
 * classes, schema cloning and generation of support classes.
 */
abstract class VirtualDocumenter extends Component
{
    /**
     * Count classes being created.
     *
     * @var int
     */
    private $countClasses = 0;

    /**
     * Namespaces aggregate entities and helper classes.
     *
     * @var NamespaceElement[]
     */
    protected $namespaces = [];

    /**
     * Helper classes used to describe collections, selectors and etc, basically everything
     * which helps entity to work. Helpers will be rendered at the end of file under specialized
     * virtual namespace.
     *
     * @var ClassElement[]
     */
    protected $helpers = [];

    /**
     * File to render virtual documentation into.
     *
     * @var FileElement
     */
    protected $file = null;

    /**
     * Stores configuration.
     *
     * @invisible
     * @var Documenter
     */
    protected $documenter = null;

    /**
     * @param Documenter     $documenter
     * @param FilesInterface $files
     */
    public function __construct(Documenter $documenter, FilesInterface $files)
    {
        $this->documenter = $documenter;

        //Documenter must declare namespace for virtual classes
        $this->file = new FileElement($files);
        $this->file->setComment($documenter->config()['header']);
    }

    /**
     * Render virtual documentation into file.
     *
     * @param string $filename
     * @return bool
     */
    public function render($filename)
    {
        return $this->file->renderTo($filename, FilesInterface::RUNTIME, true);
    }

    /**
     * Count classes being created.
     *
     * @return int
     */
    public function countClasses()
    {
        return $this->countClasses;
    }

    /**
     * Generate virtual entity representation.
     *
     * @param ReflectionEntity $entity
     * @return ClassElement
     */
    protected function renderEntity(ReflectionEntity $entity)
    {
        $element = new ClassElement($entity->getShortName());

        //We are going to render every entity property as real property due child implementation
        //may clarify it's type
        foreach ($entity->getFields() as $field => $type) {
            if (substr($field, 0, 1) == '_') {
                //Hidden fields
                continue;
            }

            $classType = '';
            $arrayType = false;
            if (is_array($type)) {
                $arrayType = true;
                $type = $type[0];
            }

            $type = str_replace('[]', '', $type);
            if (lcfirst($type) != $type && class_exists($type)) {
                $type = $classType = '\\' . $type;
            }

            if ($arrayType) {
                $type .= '[]';
            }

            $element->property($field, "@var {$type}")->setAccess(AbstractElement::ACCESS_PUBLIC);

            //Let's pre-generate getters and setters
            $setter = $element->method('set' . Inflector::classify($field));
            $getter = $element->method('get' . Inflector::classify($field));

            $setter->parameter($field, $type);
            if (!empty($arrayType)) {
                $setter->parameter($field)->setType('array');
            } elseif (!empty($classType)) {
                $setter->parameter($field)->setType($classType);
            }

            $getter->parameter('default')->setOptional(true, null);

            $setter->setComment("@return \$this", true);
            $getter->setComment("@return {$type}");
        }

        foreach ($entity->getAccessors() as $name => $accessor) {
            if (is_array($accessor)) {
                $accessor = $accessor[0];
            }

            $element->property($name, '@var \\' . $accessor);
        }

        return $element;
    }

    /**
     * Get ClassElement of specified helper class or generate new one - documenter requires method
     * named render{type}($name) (must return class element).
     *
     * Method will return full helper class name (including virtual namespace with leading slash).
     *
     * @param string $type
     * @param string $name
     * @return string
     * @throws DocumenterException
     */
    protected function helper($type, $name)
    {
        $namespace = trim($this->documenter->config()['namespace'], '\\');

        if (isset($this->helpers[$type . '.' . $name])) {
            return '\\' . $namespace . '\\' . $this->helpers[$type . '.' . $name]->getName();
        }

        if (!method_exists($this, $renderer = 'render' . ucfirst($type))) {
            throw new DocumenterException(
                "Helper class '{$name}' of type '{$type}' does not have renderer."
            );
        }

        //We must render helper class
        $this->helpers[$type . '.' . $name] = $element = call_user_func([$this, $renderer], $name);

        //Let's add element into virtual namespace
        $this->addClass($element, $this->documenter->config()['namespace']);

        return '\\' . $namespace . '\\' . $element->getName();
    }

    /**
     * Add new ClassElement into file under specified namespace.
     *
     * @param ClassElement $element
     * @param string       $namespace
     */
    protected function addClass(ClassElement $element, $namespace)
    {
        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = new NamespaceElement(trim($namespace, '\\'));
            $this->file->addNamespace($this->namespaces[$namespace]);
        }

        $this->namespaces[$namespace]->addClass($element);
        $this->countClasses++;
    }

    /**
     * Generate class name using set of name chunks provided as array of set of arguments. Name
     * will be prefixed with _ to indicate that it can not be used in real code.
     *
     * Example:
     * $this->createName('user', 'collection'); //_UserCollection
     *
     * @param array|mixed $chunks
     * @return string
     */
    protected function createName($chunks)
    {
        $chunks = is_array($chunks) ? $chunks : func_get_args();

        return '_' . Inflector::classify(str_replace('\\', '', join('-', $chunks)));
    }
}