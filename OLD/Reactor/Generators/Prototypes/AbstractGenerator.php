<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Generators\Prototypes;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\ClassElement;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\FileElement;

/**
 * Abstract reactor generator used to create class declaration.
 */
abstract class AbstractGenerator
{
    /**
     * Default filename extension.
     */
    const EXTENSION = '.php';

    /**
     * Given generation name.
     *
     * @var string
     */
    private $name = '';

    /**
     * Options must include prefix, namespace and output directory.
     *
     * @var array
     */
    protected $options = [
        'namespace' => '',
        'postfix'   => '',
        'directory' => ''
    ];

    /**
     * @var ClassElement
     */
    protected $class = null;

    /**
     * @var FileElement
     */
    protected $file = null;

    /**
     * @param FilesInterface $files
     * @param string         $name    User specified class name, will be joined with default
     *                                namespace and postfix.
     * @param array          $options Default namespace must already be included into default
     *                                directory.
     * @param string         $header  File header.
     */
    public function __construct(FilesInterface $files, $name, array $options, $header = '')
    {
        $this->options = $options + $this->options;

        if (empty($this->options['directory'])) {
            throw new ReactorException("Declaration generator always require output directory.");
        }

        $this->file = new FileElement($files);
        $this->file->setComment($header);

        $this->class = new ClassElement('Reactor');
        $this->file->addClass($this->class);

        //Configuring names
        $this->setNamespace('')->setName($name);

        //Implementation specific generation
        $this->generate();
    }

    /**
     * Set class name, name must not include default namespace and postfix.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        if (strpos($name, '/') !== false || strpos($name, '\\') !== false) {
            $name = str_replace('/', '\\', $name);

            //Let's split namespace
            $this->setNamespace(substr($name, 0, strrpos($name, '\\')));
            $name = substr($name, strrpos($name, '\\') + 1);
        }

        $this->class->setName(Inflector::classify($name) . $this->options['postfix']);

        return $this;
    }

    /**
     * Given generation name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get generated class name. Namespace will not be included.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class->getName();
    }

    /**
     * Set class namespace, default namespace will be automatically joined. Namespace will be
     * normalized.
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $namespace = str_replace('/', '\\', $namespace);

        //Cutting start and end symbols
        $namespace = trim($namespace, '\\');

        $chunks = [$this->options['namespace']];
        foreach (explode('\\', $namespace) as $chunk) {
            $chunks[] = ucfirst($chunk);
        }

        $this->file->setNamespace(trim(join('\\', $chunks), '\\'));

        return $this;
    }

    /**
     * Get generated class namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->file->getNamespace();
    }

    /**
     * Get class name including namespace and short class name.
     */
    public function getClassName()
    {
        return $this->getNamespace() . '\\' . $this->getClass();
    }

    /**
     * Check if generated class name is unique.
     */
    public function isUnique()
    {
        return !class_exists($this->getClassName());
    }

    /**
     * Class being generated.
     *
     * @return ClassElement
     */
    public function getClassElement()
    {
        return $this->class;
    }

    /**
     * Set comment for generated class.
     *
     * @param string|array $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->class->setComment($comment);

        return $this;
    }

    /**
     * File being generated.
     *
     * @return FileElement
     */
    public function getFileElement()
    {
        return $this->file;
    }

    /**
     * Render created declaration. Filename will be resolved automatically based on active namespace
     * and class name.
     *
     * @param int       $mode
     * @param bool|true $ensureDirectory
     * @return bool
     */
    public function render($mode = FilesInterface::READONLY, $ensureDirectory = true)
    {
        return $this->file->render($this->getFilename(), $mode, $ensureDirectory);
    }

    /**
     * Get filename to export rendered declaration into.
     *
     * @return ClassElement
     */
    public function getFilename()
    {
        $filename = $this->options['directory'] . FilesInterface::SEPARATOR;

        //Default namespace already included into path
        $namespace = trim(
            substr($this->file->getNamespace(), strlen($this->options['namespace']) + 1),
            '\\'
        );

        $filename .= str_replace('\\', FilesInterface::SEPARATOR, $namespace);

        return $filename . FilesInterface::SEPARATOR . $this->class->getName() . static::EXTENSION;
    }

    /**
     * Generate required class methods.
     */
    abstract protected function generate();
}