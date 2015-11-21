<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Modifiers;

use Spiral\Core\Component;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\ModifierInterface;

/**
 * Replaces [[string]] with active translation, make sure that current language included into
 * environment dependencies list.
 */
class TranslateModifier extends Component implements ModifierInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator = null;

    /**
     * @var array
     */
    protected $options = [
        'prefix'  => 'view-',
        'pattern' => '/\[\[(.*?)\]\]/s'
    ];

    /**
     * @param TranslatorInterface $translator
     * @param array               $options
     */
    public function __construct(TranslatorInterface $translator, array $options = [])
    {
        $this->translator = $translator;
        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($source, $namespace, $name)
    {
        //Translator bundle to be used
        $bundle = $this->options['prefix'] . str_replace(
                ['/', '\\'], '-', $namespace . '-' . $name
            );

        return preg_replace_callback($this->options['pattern'], function ($matches) use ($bundle) {
            return $this->translator->translate($bundle, $matches[1]);
        }, $source);
    }
}