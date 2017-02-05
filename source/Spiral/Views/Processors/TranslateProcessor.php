<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Processors;

use Spiral\Core\Component;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\SourceContextInterface;
use Spiral\Views\ViewSource;

/**
 * Replaces [[string]] with active translation, make sure that current language included into
 * environment dependencies list.
 */
class TranslateProcessor extends Component implements ProcessorInterface
{
    /**
     * @invisible
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
    public function __construct(TranslatorInterface $translator = null, array $options = [])
    {
        $this->translator = $translator;
        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(
        EnvironmentInterface $environment,
        ViewSource $view,
        string $code
    ): string {
        $bundle = "{$view->getNamespace()}-{$view->getName()}";

        //Translator options must automatically route this view name to specific domain
        $domain = $this->translator->resolveDomain(
            $this->options['prefix'] . str_replace(['/', '\\'], '-', $bundle)
        );

        //We are not forcing locale for now

        return preg_replace_callback(
            $this->options['pattern'],
            function ($matches) use ($domain) {
                return $this->translator->trans($matches[1], [], $domain);
            },
            $code
        );
    }
}