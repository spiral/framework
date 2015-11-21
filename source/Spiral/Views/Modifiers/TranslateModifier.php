<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Modifiers;

use Spiral\Core\Component;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ModifierInterface;

/**
 * Replaces [[string]] with active translation, make sure that current language included into
 * environment dependencies list.
 */
class TranslateModifier extends Component implements ModifierInterface
{
    /**
     * Sugary.
     */
    use SaturateTrait;

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
     * TranslateModifier constructor.
     *
     * @param EnvironmentInterface $environment
     * @param TranslatorInterface  $translator
     * @param array                $options
     */
    public function __construct(
        EnvironmentInterface $environment,
        TranslatorInterface $translator = null,
        array $options = []
    ) {
        $this->translator = $this->saturate($translator, TranslatorInterface::class);
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