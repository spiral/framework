<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doctrine\Common\Annotations {

    use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

    if (! \interface_exists(NamedArgumentConstructorAnnotation::class)) {
        /**
         * Marker interface for PHP7/PHP8 compatible support for named
         * arguments (and constructor property promotion).
         *
         * @deprecated Implementing this interface is deprecated.
         *             Use the Annotation @NamedArgumentConstructor instead.
         */
        interface NamedArgumentConstructorAnnotation
        {
        }
    }

}

namespace Doctrine\Common\Annotations\Annotation {

    if (! \class_exists(NamedArgumentConstructor::class, false)) {
        /**
         * Annotation that indicates that the annotated class should be
         * constructed with a named argument call.
         *
         * @Annotation
         * @Target("CLASS")
         */
        #[\Attribute(\Attribute::TARGET_CLASS)]
        class NamedArgumentConstructor
        {
        }
    }

}

namespace {

    use JetBrains\PhpStorm\ExpectedValues;

    if (! \class_exists(Attribute::class, false)) {
        /**
         * @psalm-type AttributeTarget = Attribute::TARGET_*
         * @psalm-type AttributeFlag  = AttributeTarget | Attribute::IS_REPEATABLE
         * @psalm-type AttributeFlags  = int-mask-of<AttributeFlag>
         */
        #[Attribute(Attribute::TARGET_CLASS)]
        final class Attribute
        {
            /**
             * Marks that attribute declaration is allowed only in classes.
             *
             * @var AttributeTarget
             */
            public const TARGET_CLASS = 0b00000001;

            /**
             * Marks that attribute declaration is allowed only in functions.
             *
             * @var AttributeTarget
             */
            public const TARGET_FUNCTION = 0b00000010;

            /**
             * Marks that attribute declaration is allowed only in class methods.
             *
             * @var AttributeTarget
             */
            public const TARGET_METHOD = 0b00000100;

            /**
             * Marks that attribute declaration is allowed only in class properties.
             *
             * @var AttributeTarget
             */
            public const TARGET_PROPERTY = 0b00001000;

            /**
             * Marks that attribute declaration is allowed only in class constants.
             *
             * @var AttributeTarget
             */
            public const TARGET_CLASS_CONSTANT = 0b00010000;

            /**
             * Marks that attribute declaration is allowed only in function or method parameters.
             *
             * @var AttributeTarget
             */
            public const TARGET_PARAMETER = 0b00010000;

            /**
             * Marks that attribute declaration is allowed anywhere.
             *
             * @var AttributeFlag
             */
            public const TARGET_ALL = self::TARGET_CLASS
                                    | self::TARGET_FUNCTION
                                    | self::TARGET_METHOD
                                    | self::TARGET_PROPERTY
                                    | self::TARGET_PARAMETER
            ;

            /**
             * Notes that an attribute declaration in the same place is allowed multiple times.
             *
             * @var AttributeFlag
             */
            public const IS_REPEATABLE = 0b00100000;

            /**
             * @var AttributeFlag
             */
            public $flags;

            /**
             * @param int-mask-of<AttributeFlag> $flags A value in the form of
             *        a bitmask indicating the places where attributes can be
             *        defined.
             */
            public function __construct(
                #[ExpectedValues(flagsFromClass: Attribute::class)]
                int $flags = self::TARGET_ALL
            ) {
            }
        }
    }

}
