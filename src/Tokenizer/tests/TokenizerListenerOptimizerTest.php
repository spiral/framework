<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tokenizer\Attribute\ListenAttribute;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class TokenizerListenerOptimizerTest extends TestCase
{
    public function testControllerListener(): void
    {
        $locator = new ClassLocator(
            (new Finder())->in(__DIR__ . '/Classes/Targets')->files()->name('*.php'),
        );

        $classes = $locator->getClasses();

        $listener = new ControllerListener();
        $refl = new \ReflectionClass($listener);
        foreach ($refl->getAttributes(ListenAttribute::class) as $attribute) {
            $refl = new \ReflectionClass($attribute->getArguments()['class']);

            $attr = $refl->getAttributes(\Attribute::class)[0];
            var_dump($refl);
            if ($attr->getTarget() && \Attribute::TARGET_CLASS) {
                var_dump('TARGET_CLASS');
            }

            if ($attr->getTarget() && \Attribute::TARGET_METHOD) {
                var_dump('TARGET_METHOD');
            }

            if ($attr->getTarget() && \Attribute::TARGET_PROPERTY) {
                var_dump('TARGET_PROPERTY');
            }
        }
    }
}
