<?php
/**
 * Spiral Framework, Core Components
 *
 * @author    Dmitry Mironov <dmitry.mironov@spiralscout.com>
 */

namespace Spiral\Tests\Validation;


use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Debug\LogsInterface;
use Spiral\Models\DataEntity;
use Spiral\Tests\Validation\Fixtures\IsLoadedCondition;
use Spiral\Translator\TranslatorInterface;
use Spiral\Validation\Checkers\AddressChecker;
use Spiral\Validation\Checkers\TypeChecker;
use Spiral\Validation\Configs\ValidatorConfig;
use Spiral\Validation\Validator;
use TestApplication\Database\SampleRecord;

/**
 * Class ValidatorTest
 *
 * @package Spiral\Tests\Validation
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = [
        'emptyConditions' => [
            'notEmpty',
            'type::notEmpty',
        ],
        'checkers'        => [
            'type'    => TypeChecker::class,
            'address' => AddressChecker::class,
        ],
        'aliases'         => [
            'notEmpty' => 'type::notEmpty',
            'email'    => 'address::email',
            'url'      => 'address::url',
        ],
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    private $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LogsInterface
     */
    private $logs;

    /**
     * @var ValidatorConfig
     */
    private $config;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logs = $this->createMock(LogsInterface::class);

        $this->translator->method('trans')->willReturnCallback(function ($string) {
            return $string;
        });

        $this->container->method('has')->willReturnCallback(function ($dep) {
            switch ($dep) {
                case TranslatorInterface::class:
                case LogsInterface::class:
                case TypeChecker::class:
                    return true;
                default:
                    return false;
            }
        });
        $this->container->method('get')->willReturnCallback(function ($dep) {
            switch ($dep) {
                case TranslatorInterface::class:
                    return $this->translator;
                case LogsInterface::class:
                    return $this->logs;
                case TypeChecker::class:
                    return new TypeChecker($this->container);
                case AddressChecker::class:
                    return new AddressChecker($this->container);
                default:
                    // it actually must throw NotFoundException (interface), but it will not
                    // because there is no real reason for SomeException (interface)
                    throw new \RuntimeException();
            }
        });

        $this->config = new ValidatorConfig(static::CONFIG);
    }

    public function testNoRules()
    {
        $validator = new Validator([], [], $this->config, $this->container);
        $this->assertTrue($validator->isValid());

        $validator->setData(['email' => 'user@example.com']);
        $this->assertTrue($validator->isValid());
    }

    public function testEmptyConditions()
    {
        $validator = new Validator(['name' => ['type::notEmpty']], [], $this->config,
            $this->container);
        $this->assertFalse($validator->isValid());

        $validator->setData(['name' => null]);
        $this->assertFalse($validator->isValid());

        $validator->setData(['name' => '']);
        $this->assertFalse($validator->isValid());

        $validator->setData(['name' => 'John Doe']);
        $this->assertTrue($validator->isValid());
    }

    public function testRegularChecker()
    {
        $validator = new Validator(['email' => ['address::email']], [], $this->config,
            $this->container);
        $this->assertTrue($validator->isValid());

        $validator->setData(['email' => null]);
        $this->assertTrue($validator->isValid());

        $validator->setData(['email' => '']);
        $this->assertTrue($validator->isValid());

        $validator->setData(['email' => 'john.doe@example.com']);
        $this->assertTrue($validator->isValid());

        $validator->setData(['email' => 'example.com']);
        $this->assertFalse($validator->isValid());
    }

    public function testAliasesAndClasses()
    {
        $validator = new Validator([], [], $this->config, $this->container);

        $validator->setRules(['name' => ['notEmpty']]);
        $validator->setData(['name' => null]);
        $this->assertFalse($validator->isValid());
        $validator->setData(['name' => 'John Doe']);
        $this->assertTrue($validator->isValid());

        $validator->setRules(['email' => ['email']]);
        $validator->setData(['email' => 'john.doe@example.com']);
        $this->assertTrue($validator->isValid());
        $validator->setData(['email' => 'John Doe']);
        $this->assertFalse($validator->isValid());

        $validator->setRules(['url' => [[[AddressChecker::class, 'url']]]]);
        $validator->setData(['url' => 'example.com']);
        $this->assertFalse($validator->isValid());
        $validator->setData(['url' => 'http://example.com']);
        $this->assertTrue($validator->isValid());
    }

    public function testNotAliasChecker()
    {
        $config = new ValidatorConfig([
            'emptyConditions' => [],
            'checkers'        => [],
            'aliases'         => []
        ]);
        $validator = new Validator([], [], $config, $this->container);

        //Test rule without arguments
        $validator->setRules(['url' => [AddressChecker::class . '::url']]);

        $validator->setData(['url' => 'http://example.com']);
        $this->assertTrue($validator->isValid());

        $validator->setData(['url' => 'example.com']);
        $this->assertFalse($validator->isValid());

        $this->assertEquals(
            substr(AddressChecker::MESSAGES['url'], 2, -2),
            $validator->getErrors()['url']
        );

        //Test rule with arguments
        $validator->setRules(['url' => [[AddressChecker::class . '::url', false]]]);

        $validator->setData(['url' => 'example.com']);
        $this->assertTrue($validator->isValid());

        $validator->setData(['url' => 'before:after']);
        $this->assertFalse($validator->isValid());

        $this->assertEquals(
            substr(AddressChecker::MESSAGES['url'], 2, -2),
            $validator->getErrors()['url']
        );
    }

    public function testRuleChain()
    {
        $validator = new Validator(['email' => ['notEmpty', 'email']], [],
            $this->config, $this->container);

        $validator->setData([]);
        $this->assertEquals(substr(TypeChecker::MESSAGES['notEmpty'], 2, -2),
            $validator->getErrors()['email']);

        $validator->setData(['email' => 'example.com']);
        $this->assertEquals(substr(AddressChecker::MESSAGES['email'], 2, -2),
            $validator->getErrors()['email']);
    }

    public function testErrorsLogging()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        $this->logs->method('getLogger')->willReturn($logger);

        $validator = new Validator(['name' => [[[\stdClass::class, 'check']]]], ['name' => 'John'],
            $this->config, $this->container);
        $validator->isValid();
    }

    public function testErrorTranslation()
    {
        $validator = new Validator(['email' => ['address::email']], ['email' => 'example.com'],
            $this->config, $this->container);
        $this->assertFalse($validator->isValid());
        $this->assertEquals(substr(AddressChecker::MESSAGES['email'], 2, -2),
            $validator->getErrors()['email']);

        $validator->setRules(['email' => [['address::email', 'message' => 'A message']]]);
        $this->assertFalse($validator->isValid());
        $this->assertEquals('A message', $validator->getErrors()['email']);

        $validator->setRules(['email' => [['address::email', 'error' => 'A message']]]);
        $this->assertFalse($validator->isValid());
        $this->assertEquals('A message', $validator->getErrors()['email']);
    }

    public function testSetRules()
    {
        $rules = ['email' => ['address::email']];
        $validator = new Validator($rules, ['email' => 'example.com'], $this->config,
            $this->container);

        $this->assertTrue($validator->hasErrors());
        $this->assertEquals($validator, $validator->setRules($rules));
        $this->assertTrue($validator->hasErrors());
        $this->assertEquals($validator, $validator->setRules([]));
        $this->assertFalse($validator->hasErrors());
    }

    public function testSetData()
    {
        $data = ['email' => 'example.com'];
        $validator = new Validator(['email' => ['address::email']], $data,
            $this->config, $this->container);

        $this->assertTrue($validator->hasErrors());
        $this->assertEquals($validator, $validator->setData($data));
        $this->assertTrue($validator->hasErrors());
        $this->assertEquals($validator, $validator->setData([]));
        $this->assertFalse($validator->hasErrors());

        $this->assertEquals(['foo' => 'bar'], $validator->setData(['foo' => 'bar'])->getData());
    }

    public function testSetDataWithAnEntity()
    {
        $data = new DataEntity([
            "name" => "Spiral",
            "type" => "Framework",
            "foo"  => "bar"
        ]);

        $val = new Validator([], [], $this->config, $this->container);
        $validator = $val->setData($data);

        $this->assertFalse($validator->hasErrors()); //No rules were registered
        $this->assertEquals($data->getFields(), $validator->getData());
    }

    public function testCustomErrors()
    {
        $validator = new Validator(['email' => ['address::email']], ['email' => 'example.com'],
            $this->config, $this->container);

        $this->assertFalse($validator->isValid());
        $this->assertEquals(substr(AddressChecker::MESSAGES['email'], 2, -2),
            $validator->getErrors()['email']);

        $this->assertEquals($validator, $validator->registerError('email', 'An error message'));
        $this->assertEquals('An error message', $validator->getErrors()['email']);

        $this->assertEquals($validator, $validator->flushRegistered());
        $this->assertEquals(substr(AddressChecker::MESSAGES['email'], 2, -2),
            $validator->getErrors()['email']);
    }

    public function testContext()
    {
        $context = new \StdClass();
        $context->data = 'some context';

        $validator = new Validator([], [], $this->config, $this->container);
        $validator->setContext($context);

        $this->assertEquals($context, $validator->getContext());
    }

    public function testWithConditions()
    {
        $validator = new Validator(
            ['email' => ['notEmpty', 'address::email']],
            ['email' => 'some@email.com'],
            $this->config,
            $this->container
        );
        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => IsLoadedCondition::class],
                    ['address::email', 'condition' => IsLoadedCondition::class],
                ]
            ],
            ['email' => null],
            $this->config,
            $this->container
        );
        $this->assertFalse($validator->isValid());

        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => IsLoadedCondition::class],
                    ['address::email', 'condition' => IsLoadedCondition::class],
                ]
            ],
            ['email' => null],
            $this->config,
            $this->container
        );
        $validator->setContext(['some', 'context']);
        //should be entity
        $this->assertFalse($validator->isValid());

        $entity = new SampleRecord();
        $validator->setContext($entity);
        //should be loaded
        $this->assertFalse($validator->isValid());

        $entity = new SampleRecord();
        $entity->save();
        $validator->setContext($entity);
        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => 'Some\Condition'],
                    ['address::email', 'condition' => 'Some\Condition'],
                ]
            ],
            ['email' => null],
            $this->config,
            $this->container
        );

        $entity = new SampleRecord();
        $entity->save();

        $validator->setContext($entity);
        //should exist and be instance of \Spiral\Validation\CheckerConditionInterface::class
        $this->assertFalse($validator->isValid());
    }
}