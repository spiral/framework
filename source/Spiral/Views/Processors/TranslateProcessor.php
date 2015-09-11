<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Core\Component;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\Compiler;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

/**
 * Performs string replacement in view source using translator instance and [[ ]] pattern. Processor
 * will generate translator bundle name using view name and namespace.
 */
class TranslateProcessor extends Component implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $options = [
        'prefix'  => 'view-',
        'pattern' => '/\[\[(.*?)\]\]/s'
    ];

    /**
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * @var TranslatorInterface
     */
    protected $translator = null;

    /**
     * @var string
     */
    protected $bundle = '';

    /**
     * {@inheritdoc}
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ViewManager $views,
        Compiler $compiler,
        array $options,
        TranslatorInterface $translator = null
    ) {
        $this->compiler = $compiler;
        $this->options = $options + $this->options;

        if ($this->compiler->getNamespace() != ViewsInterface::DEFAULT_NAMESPACE) {
            $this->bundle = $compiler->getNamespace();
        }

        //I18n namespace constructed using view name, view namespace and prefix
        $this->bundle .= '-' . $this->options['prefix'] . str_replace(
                ['/', '\\'], '-', $compiler->getView()
            );

        $this->bundle = trim($this->bundle, '-');

        //Global container as fallback
        $this->translator = self::saturate(TranslatorInterface::class, $translator);
    }

    /**
     * {@inheritdoc}
     */
    public function process($source)
    {
        return preg_replace_callback($this->options['pattern'], [$this, 'replace'], $source);
    }

    /**
     * @param array $matches
     * @return string
     */
    protected function replace($matches)
    {
        return $this->translator->translate($this->bundle, $matches[1]);
    }
}