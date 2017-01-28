<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Validation\Checkers;

use Interop\Container\ContainerInterface;
use Mockery as m;
use Spiral\Core\Container;
use Spiral\Translator\TranslatorInterface;
use Spiral\Validation\Checkers;
use Spiral\Validation\Configs\ValidatorConfig;
use Spiral\Validation\Validator;

class RequiredCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testRequiredWith()
    {
        $rules = [
            'a' => [
                ['notEmpty']
            ],
            'b' => [
                ['required::with', 'a']
            ]
        ];

        //BOOLEAN TREE TEST
        $this->assertFail('a', [
        ], $rules);

        $this->assertFail('b', [
            'a' => true,
            'b' => null
        ], $rules);

        $this->assertValid([
            'a' => true,
            'b' => true
        ], $rules);
    }

    public function testRequiredWithMultiple()
    {
        $rules = [
            'a' => [
                ['notEmpty']
            ],
            'b' => [
                ['required::with', ['a', 'c']]
            ]
        ];

        //BOOLEAN TREE TEST
        $this->assertFail('a', [
        ], $rules);

        $this->assertFail('b', [
            'a' => true,
            'b' => null
        ], $rules);

        $this->assertFail('b', [
            'c' => true,
            'b' => null
        ], $rules);

        $this->assertValid([
            'a' => true,
            'b' => true
        ], $rules);

        $this->assertFail('a', [
            'c' => true,
            'b' => true
        ], $rules);
    }

    public function testRequiredWithAll()
    {
        $rules = [
            'a' => [
                ['required::withAll', ['b', 'c']]
            ]
        ];

        //BOOLEAN TREE TEST
        $this->assertValid([], $rules);

        $this->assertValid([
            'a' => true,
            'b' => null
        ], $rules);

        $this->assertValid([
            'a' => null,
            'c' => true
        ], $rules);

        $this->assertFail('a', [
            'b' => true,
            'c' => true
        ], $rules);

        $this->assertValid([
            'a' => true,
            'b' => true,
            'c' => true
        ], $rules);
    }

    public function testRequiredWithout()
    {
        $rules = [
            'a' => [
                ['required::without', ['b', 'c']]
            ]
        ];

        //BOOLEAN TREE TEST

        $this->assertFail('a', [], $rules);

        $this->assertValid([
            'b' => true
        ], $rules);

        $this->assertValid([
            'c' => true
        ], $rules);

        $this->assertValid([
            'b' => true,
            'c' => true
        ], $rules);

        $this->assertFail('a', [
            'b' => null,
            'c' => null
        ], $rules);
    }

    public function testRequiredWithoutAll()
    {
        $rules = [
            'a' => [
                ['required::withoutAll', ['b', 'c']]
            ]
        ];

        //BOOLEAN TREE TEST

        $this->assertFail('a', [], $rules);

        $this->assertValid([
            'b' => true
        ], $rules);

        $this->assertValid([
            'c' => true
        ], $rules);

        $this->assertValid([
            'b' => true,
            'c' => true
        ], $rules);

        $this->assertFail('a', [
            'b' => null,
            'c' => null
        ], $rules);
    }

    protected function assertValid(array $data, array $rules)
    {
        $validator = $this->createValidator($rules);
        $validator->setData($data);

        $this->assertTrue($validator->isValid(), 'Validation FAILED');
    }

    protected function assertFail(string $error, array $data, array $rules)
    {
        $validator = $this->createValidator($rules);
        $validator->setData($data);

        $this->assertFalse($validator->isValid(), 'Validation PASSED');
        $this->assertArrayHasKey($error, $validator->getErrors());
    }

    /**
     * @param array $rules
     * @param array $data
     *
     * @return Validator
     */
    protected function createValidator(array $rules, array $data = [])
    {
        $config = new ValidatorConfig([
            /*
               * Set of empty conditions which tells Validator what rules to be counted as "stop if empty",
               * without such condition field validations will be skipped if value is empty.
               */
            'emptyConditions' => [
                "notEmpty",
                "required",
                "type::notEmpty",
                "required::with",
                "required::without",
                "required::withAll",
                "required::withoutAll",
                "file::exists",
                "file::uploaded",
                "image::exists",
                "image::uploaded",
                /*{{empties}}*/
            ],
            /*
             * Checkers are resolved using container and provide ability to isolate some validation rules
             * under common name and class. You can register new checkers at any moment without any
             * performance issues.
             */
            'checkers'        => [
                "type"     => Checkers\TypeChecker::class,
                "required" => Checkers\RequiredChecker::class,
                "number"   => Checkers\NumberChecker::class,
                "mixed"    => Checkers\MixedChecker::class,
                "address"  => Checkers\AddressChecker::class,
                "string"   => Checkers\StringChecker::class,
                "file"     => Checkers\FileChecker::class,
                "image"    => Checkers\ImageChecker::class,
                /*{{checkers}}*/
            ],
            /*
             * Aliases are only used to simplify developer life.
             */
            'aliases'         => [
                "notEmpty"   => "type::notEmpty",
                "required"   => "type::notEmpty",
                "datetime"   => "type::datetime",
                "timezone"   => "type::timezone",
                "bool"       => "type::boolean",
                "boolean"    => "type::boolean",
                "cardNumber" => "mixed::cardNumber",
                "regexp"     => "string::regexp",
                "email"      => "address::email",
                "url"        => "address::url",
                "file"       => "file::exists",
                "uploaded"   => "file::uploaded",
                "filesize"   => "file::size",
                "image"      => "image::valid",
                "array"      => "is_array",
                "callable"   => "is_callable",
                "double"     => "is_double",
                "float"      => "is_float",
                "int"        => "is_int",
                "integer"    => "is_integer",
                "numeric"    => "is_numeric",
                "long"       => "is_long",
                "null"       => "is_null",
                "object"     => "is_object",
                "real"       => "is_real",
                "resource"   => "is_resource",
                "scalar"     => "is_scalar",
                "string"     => "is_string",
                "match"      => "mixed::match",
                /*{{aliases}}*/
            ]
        ]);

        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('resolveDomain')->andReturn('domain');
        $translator->shouldReceive('trans')->andReturn('error');

        $container = new Container();
        $container->bind(ContainerInterface::class, $container);
        $container->bind(TranslatorInterface::class, $translator);

        return new Validator($rules, $data, $config, $container);
    }
}