<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\I18n\Translator;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class I18nProcessor implements ProcessorInterface
{
    /**
     * Processor options. Will define i18n namespace prefix and expression to be treated as text to
     * localize.
     *
     * @var array
     */
    protected $options = array(
        'prefix'  => 'view-',
        'pattern' => '/\[\[(.*?)\]\]/s'
    );

    /**
     * Current view namespace, this namespace is not identical to view rendering namespaces, this is
     * i18n localization namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * View component.
     *
     * @var ViewManager
     */
    protected $view = null;

    /**
     * I18n component instance.
     *
     * @var Translator
     */
    protected $i18n = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param array       $options
     * @param ViewManager $compiler View component instance (if presented).
     * @param Translator  $i18n Translator component instance.
     */
    public function __construct(array $options, ViewManager $compiler = null, Translator $i18n = null)
    {
        $this->options = $options + $this->options;
        $this->view = $compiler;
        $this->i18n = $i18n;
    }

    /**
     * Performs i18n replaces for text in views. This processor should be called first, due templater
     * combinations many new namespaces will be created, even if text inside them will be identical
     * and inherited from parent view.
     *
     * @param string $source    View source (code).
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @return string
     * @throws \ErrorException
     */
    public function processSource($source, $namespace,$view)
    {
        $this->namespace = ($namespace != ViewManager::DEFAULT_NAMESPACE ? $namespace . '-' : '');
        $this->namespace .= $this->options['prefix'] . str_replace(array('/', '\\'), '-', $view);

        //Doing replacement
        $source = preg_replace_callback($this->options['pattern'], array($this, 'replace'), $source);

        return $source;
    }

    /**
     * Translation and replacement.
     *
     * @param array $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->i18n->get($this->namespace, $matches[1]);
    }
}