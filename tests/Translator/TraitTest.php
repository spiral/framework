<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Translator;

use Mockery as m;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\SampleComponent;
use Spiral\Tests\Core\Fixtures\SharedComponent;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\TranslatorInterface;

class TraitTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SharedComponent::shareContainer(null);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     * @expectedExceptionMessage Unable to get instance of 'TranslatorInterface'
     */
    public function testNoContainer()
    {
        $class = new SayClass();
        $class->saySomething();
    }

    public function testSayWithContainer()
    {
        $container = new Container();
        $container->bind(
            TranslatorInterface::class,
            $translator = m::mock(TranslatorInterface::class)
        );

        SharedComponent::shareContainer($container);

        $translator->shouldReceive('resolveDomain')->with(SayClass::class)->andReturn('say-class');
        $translator->shouldReceive('trans')->with('Something', [], 'say-class')->andReturn(
            'Translated Something'
        );

        $class = new SayClass();
        $this->assertSame('Translated Something', $class->saySomething());
    }

    public function testMessageWithContainer()
    {
        $container = new Container();
        $container->bind(
            TranslatorInterface::class,
            $translator = m::mock(TranslatorInterface::class)
        );

        SharedComponent::shareContainer($container);

        $translator->shouldReceive('resolveDomain')->with(SayClass::class)->andReturn('say-class');
        $translator->shouldReceive('trans')->with(
            'Hello, {name}!',
            ['name' => 'Anton'],
            'say-class'
        )->andReturn('Hello, Anton?');

        $class = new SayClass();
        $this->assertSame('Hello, Anton?', $class->sayMessage('Anton'));
    }
}

class SayClass extends Component
{
    use TranslatorTrait;

    /**
     * Static message.
     */
    const MESSAGE = '[[Hello, {name}!]]';

    /**
     * @return string
     */
    public function saySomething()
    {
        return $this->say('Something');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function sayMessage($name)
    {
        return $this->say(self::MESSAGE, compact('name'));
    }
}