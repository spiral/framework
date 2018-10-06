<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig;

use Spiral\Views\ContextInterface;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Traits\ProcessorTrait;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

class TwigLoader implements TwigLoaderInterface
{
    use ProcessorTrait;

    /** @var LoaderInterface */
    private $loader;

    /** @var ContextInterface */
    private $context;

    /**
     * @param LoaderInterface $loader
     * @param array           $processors
     */
    public function __construct(LoaderInterface $loader, array $processors)
    {
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * Lock loader to specific context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        if (empty($this->context)) {
            throw new EngineException("Unable to use TwigLoader without given context.");
        }

        // Apply processors
        $source = $this->process($this->loader->load($name), $this->context);

        return new \Twig_Source(
            $source->getCode(),
            $source->getName(),
            $source->getFilename()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        $filename = $this->loader->load($name)->getFilename();

        return sprintf(
            "%s.%s.%s",
            $filename,
            filemtime($filename),
            $this->context->getID()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->loader->load($name)->getFilename()) < $time;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->loader->exists($name);
    }
}