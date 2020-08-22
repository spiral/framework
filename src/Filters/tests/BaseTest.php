<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Filters\FilterProvider;
use Spiral\Validation\Checker\AddressChecker;
use Spiral\Validation\Checker\FileChecker;
use Spiral\Validation\Checker\ImageChecker;
use Spiral\Validation\Checker\StringChecker;
use Spiral\Validation\Checker\TypeChecker;
use Spiral\Validation\Config\ValidatorConfig;
use Spiral\Validation\ParserInterface;
use Spiral\Validation\RuleParser;
use Spiral\Validation\RulesInterface;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

abstract class BaseTest extends TestCase
{
    public const VALIDATION_CONFIG = [
        'checkers' => [
            'file'    => FileChecker::class,
            'image'   => ImageChecker::class,
            'type'    => TypeChecker::class,
            'address' => AddressChecker::class,
            'string'  => StringChecker::class
        ],
        'aliases'  => [
            'notEmpty' => 'type::notEmpty',
            'email'    => 'address::email',
            'url'      => 'address::url',
        ],
    ];

    protected $container;

    public function setUp(): void
    {
        $this->container = new Container();

        $this->container->bindSingleton(ValidationInterface::class, ValidationProvider::class);
        $this->container->bindSingleton(RulesInterface::class, ValidationProvider::class);
        $this->container->bindSingleton(ParserInterface::class, RuleParser::class);

        $this->container->bind(
            ValidatorConfig::class,
            new ValidatorConfig(static::VALIDATION_CONFIG)
        );
    }

    protected function getProvider(): FilterProvider
    {
        return new FilterProvider($this->container->get(ValidationInterface::class));
    }
}
