<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

use Spiral\Stempler\Syntaxes\WooSyntax;

/**
 * Provides ability to compose multiple html files together.
 */
class Stempler
{
    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var SyntaxInterface
     */
    protected $syntax = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param LoaderInterface $loader
     * @param string          $syntax Syntax class to be used.
     * @param array           $options
     */
    public function __construct($loader, $syntax = WooSyntax::class, array $options = [])
    {
        $this->loader = $loader;
        $this->syntax = new $syntax(!empty($options['strict']));
        $this->options = $options;
    }

    /**
     * Compile path.
     *
     * @param string $path
     * @return string
     */
    public function compile($path)
    {
        return $this->supervisor()->createNode($path);
    }

    /**
     * Compile string template.
     *
     * @param string $source
     * @return string
     */
    public function compileString($source)
    {
        $node = new Node($this->supervisor(), 'root', $source);

        return $node->compile();
    }

    /**
     * Create new instance of supervisor.
     *
     * @return Supervisor
     */
    protected function supervisor()
    {
        return new Supervisor($this->loader, $this->syntax);
    }
}