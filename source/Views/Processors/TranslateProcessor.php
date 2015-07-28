<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Translator\TranslatorInterface;
use Spiral\Views\Compiler\Compiler;
use Spiral\Views\Compiler\ProcessorInterface;
use Spiral\Views\ViewsInterface;

class TranslateProcessor implements ProcessorInterface
{
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
     * Active compiler.
     *
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * I18n component instance.
     *
     * @var TranslatorInterface
     */
    protected $translator = null;

    /**
     * Current translator bundle.
     *
     * @var string
     */
    protected $bundle = '';

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewsInterface      $views
     * @param Compiler            $compiler Compiler instance.
     * @param array               $options
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ViewsInterface $views,
        Compiler $compiler,
        array $options,
        TranslatorInterface $translator = null
    )
    {
        $this->compiler = $compiler;
        $this->options = $options + $this->options;

        $this->translator = !empty($translator) ? $translator : $this->compiler->getContainer()->get(
            TranslatorInterface::class
        );

        if ($this->compiler->getNamespace() != ViewsInterface::DEFAULT_NAMESPACE)
        {
            $this->bundle = $compiler->getNamespace();
        }

        //I18n namespace constructed using view name, view namespace and prefix
        $this->bundle .= '-' . $this->options['prefix'] . str_replace(
                ['/', '\\'], '-', $compiler->getView()
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
        return $this->translator->translate($this->bundle, $matches[1]);
    }
}