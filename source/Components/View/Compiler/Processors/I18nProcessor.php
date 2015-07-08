<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\I18n\Translator;
use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class I18nProcessor implements ProcessorInterface
{
    /**
     * Active compiler.
     *
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * Processor options.
     *
     * @var array
     */
    protected $options = [
        'prefix'  => 'view-',
        'pattern' => '/\[\[(.*?)\]\]/s'
    ];

    /**
     * I18n component instance.
     *
     * @var Translator
     */
    protected $i18n = null;

    /**
     * Current i18n namespace.
     *
     * @var string
     */
    protected $i18nNamespace = '';

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager $viewManager
     * @param Compiler    $compiler SpiralCompiler instance.
     * @param array       $options
     * @param Translator  $i18n
     */
    public function __construct(
        ViewManager $viewManager,
        Compiler $compiler,
        array $options,
        Translator $i18n = null
    )
    {
        $this->compiler = $compiler;
        $this->options = $options + $this->options;

        $this->i18n = !empty($i18n) ? $i18n : Translator::getInstance($viewManager->getContainer());

        //Getting i18n namespace value
        $this->i18nNamespace = $compiler->getNamespace();
        if (!$this->i18nNamespace == ViewManager::DEFAULT_NAMESPACE)
        {
            $this->i18nNamespace = '';
        }

        //I18n namespace constructed using view name, view namespace and prefix
        $this->i18nNamespace .= '-' . $this->options['prefix'] . str_replace(
                ['/', '\\'],
                '-',
                $compiler->getView()
            );
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source View source (code).
     * @return string
     */
    public function process($source)
    {
        return preg_replace_callback($this->options['pattern'], [$this, 'replace'], $source);
    }

    /**
     * Translation and replacement.
     *
     * @param array $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->i18n->get($this->i18nNamespace, $matches[1]);
    }
}