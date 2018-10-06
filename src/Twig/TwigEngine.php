<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig;


use Spiral\Core\ContainerScope;
use Spiral\Twig\Exception\SyntaxException;
use Spiral\Views\ContextInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\LocaleProcessor;
use Spiral\Views\Processor\ContextProcessor;
use Spiral\Views\ViewInterface;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;

class TwigEngine implements EngineInterface
{
    const EXTENSION = 'twig';

    /** @var bool|null|TwigCache */
    private $cache = false;

    /** @var LoaderInterface */
    private $loader;

    /** @var Environment */
    private $twig;

    /** @var TwigLoader */
    private $twigLoader;

    /**
     * @param TwigCache|null $cache
     */
    public function __construct(TwigCache $cache = null)
    {
        $this->cache = $cache ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        $engine = clone $this;
        $engine->loader = $loader->withExtension(static::EXTENSION);

        // todo: add processors
        $engine->twigLoader = new TwigLoader(
            $engine->loader,
            [
                new ContextProcessor(),
                ContainerScope::getContainer()->get(LocaleProcessor::class)
            ]
        );

        $engine->twig = new Environment($engine->twigLoader);
        $engine->twig->setCache($this->cache);

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(): LoaderInterface
    {
        if (empty($this->loader)) {
            throw new EngineException("No associated loader found.");
        }

        return $this->loader;
    }

    /**
     * @inheritdoc
     */
    public function compile(string $path, ContextInterface $context): TemplateWrapper
    {
        try {
            $this->twigLoader->setContext($context);
            $path = $this->normalize($path);

            return $this->twig->load($path);
        } catch (SyntaxError $exception) {
            //Let's clarify exception location
            throw SyntaxException::fromTwig($exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function reset(string $path, ContextInterface $context)
    {
        $this->twigLoader->setContext($context);
        $path = $this->normalize($path);

        $this->cache->delete($path, $this->twig->getTemplateClass($path));
    }

    /**
     * @inheritdoc
     */
    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return new TwigView($this->compile($path, $context));
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalize(string $path): string
    {
        $path = $this->loader->load($path);

        return sprintf("%s:%s", $path->getNamespace(), $path->getName());
    }
}