<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Translator\Views;

use Spiral\Translator\TranslatorInterface;
use Spiral\Views\ContextInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Injects locale values into the template based on locale specified by the context.
 */
final class LocaleProcessor implements ProcessorInterface
{
    private const PREFIX = 'view';
    private const REGEXP = '/\[\[(.*?)\]\]/s';

    /** @var TranslatorInterface */
    private $translator = null;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        //Translator options must automatically route this view name to specific domain
        $domain = $this->translator->getDomain(sprintf(
            "%s-%s-%s",
            self::PREFIX,
            str_replace(['/', '\\'], '-', $source->getNamespace()),
            str_replace(['/', '\\'], '-', $source->getName())
        ));

        //We are not forcing locale for now
        return $source->withCode(preg_replace_callback(
            self::REGEXP,
            function ($matches) use ($domain, $context) {
                return $this->translator->trans(
                    $matches[1],
                    [],
                    $domain,
                    $context->resolveValue(LocaleDependency::NAME)
                );
            },
            $source->getCode()
        ));
    }
}
