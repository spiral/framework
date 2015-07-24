<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\Tokenizer;

use Spiral\Components\Debug\Debugger;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Reflection\FunctionUsage\Argument;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Configurator;
use Spiral\Core\Container;
use Spiral\Core\Loader;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\RuntimeCache;

class TokenizerTest extends TestCase
{
    /**
     * @var Loader
     */
    protected $loader = null;

    /**
     * Configured tokenizer component.
     *
     * @param array $config
     * @return Tokenizer
     * @throws \Spiral\Core\CoreException
     */
    protected function getTokenizer(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'directories' => [__DIR__],
                'exclude'     => ['XX']
            ];
        }

        return new Tokenizer(
            new Configurator(['tokenizer' => $config]),
            new RuntimeCache(),
            new FileManager(),
            $this->loader
        );
    }

    protected function setUp()
    {
        $this->loader = new Loader(new RuntimeCache());

        Container::getInstance()->bind(Debugger::class, new Debugger(
            new Configurator([
                'debug' => [
                    'loggers' => [
                        'containers' => [
                        ]
                    ]
                ]
            ]),
            Container::getInstance()
        ));
    }

    protected function tearDown()
    {
        $this->loader->disable();
        $this->loader = null;
    }

    public function testClassesAll()
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->getClasses();
        $this->assertArrayHasKey(__CLASS__, $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);

        //Excluded
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testClassesByNamespace()
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $classes = $tokenizer->getClasses(null, 'Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testClassesByInterface()
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Components\Tokenizer\TestInterface');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testClassesByTrait()
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Components\Tokenizer\TestTrait');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testClassesByClassA()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testClassesByClassB()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->getClasses('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB');
        $this->assertArrayNotHasKey(__CLASS__, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassA', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassB', $classes);
        $this->assertArrayHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\ClassC', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Cases\Components\Tokenizer\Classes\Inner\ClassD', $classes);
    }

    public function testFileReflection()
    {
        $reflection = $this->getTokenizer()->fileReflection(__FILE__);

        $this->assertContains(__CLASS__, $reflection->getClasses());

        $functionUsages = $reflection->functionUsages();

        $functionA = null;
        $functionB = null;

        foreach ($functionUsages as $usage)
        {
            if ($usage->getFunction() == 'test_function_a')
            {
                $functionA = $usage;
            }

            if ($usage->getFunction() == 'test_function_b')
            {
                $functionB = $usage;
            }
        }

        $this->assertNotEmpty($functionA);
        $this->assertNotEmpty($functionB);

        $this->assertSame(2, count($functionA->getArguments()));
        $this->assertSame(Argument::VARIABLE, $functionA->getArgument(0)->getType());
        $this->assertSame('$this', $functionA->getArgument(0)->getValue());

        $this->assertSame(Argument::EXPRESSION, $functionA->getArgument(1)->getType());
        $this->assertSame('$a+$b', $functionA->getArgument(1)->getValue());

        $this->assertSame(2, count($functionB->getArguments()));

        $this->assertSame(Argument::STRING, $functionB->getArgument(0)->getType());
        $this->assertSame('"string"', $functionB->getArgument(0)->getValue());
        $this->assertSame('string', $functionB->getArgument(0)->stringValue());

        $this->assertSame(Argument::CONSTANT, $functionB->getArgument(1)->getType());
        $this->assertSame('123', $functionB->getArgument(1)->getValue());

        if (false)
        {
            $a = $b = null;
            test_function_a($this, $a + $b);
            test_function_b("string", 123);
        }
    }
}

trait TestTrait
{

}

interface TestInterface
{

}