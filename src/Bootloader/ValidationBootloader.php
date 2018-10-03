<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Validation\Checker;
use Spiral\Validation\Condition;
use Spiral\Validation\ParserInterface;
use Spiral\Validation\RuleParser;
use Spiral\Validation\RulesInterface;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

class ValidationBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        ValidationInterface::class => ValidationProvider::class,
        RulesInterface::class      => ValidationProvider::class,
        ParserInterface::class     => RuleParser::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     *
     * @throws \Spiral\Core\Exception\ConfiguratorException
     */
    public function boot(ConfiguratorInterface $configurator)
    {
        $configurator->setDefaults('validation', [
            // Checkers are resolved using container and provide ability to isolate some validation rules
            // under common name and class. You can register new checkers at any moment without any
            // performance issues.
            'checkers'   => [
                "type"    => Checker\TypeChecker::class,
                "number"  => Checker\NumberChecker::class,
                "mixed"   => Checker\MixedChecker::class,
                "address" => Checker\AddressChecker::class,
                "string"  => Checker\StringChecker::class,
                "file"    => Checker\FileChecker::class,
                "image"   => Checker\ImageChecker::class,
            ],

            // Enable/disable validation conditions
            'conditions' => [
                'withAny'    => Condition\WithAnyCondition::class,
                'withoutAny' => Condition\WithoutAnyCondition::class,
                'withAll'    => Condition\WithAllCondition::class,
                'withoutAll' => Condition\WithoutAllCondition::class,
            ],

            // Aliases are only used to simplify developer life.
            'aliases'    => [
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
            ]
        ]);

        $configurator->modify('tokenizer', new AppendPatch(
            'directories',
            null,
            directory('vendor') . 'spiral/validation/src/'
        ));
    }
}