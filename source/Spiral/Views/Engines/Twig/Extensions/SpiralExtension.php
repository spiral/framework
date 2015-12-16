<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Twig\Extensions;

use Spiral\Core\ContainerInterface;

/**
 * Provides simplified access to spiral container in twig templates using "spiral(id)" function.
 */
class SpiralExtension extends \Twig_Extension
{
    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('spiral', function ($alias) {
                return $this->container->get($alias);
            }),
            new \Twig_SimpleFunction('dump', 'dump')
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'spiral';
    }
}
