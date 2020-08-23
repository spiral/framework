<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\NamespaceDeclaration;
use Spiral\Reactor\Partial;

class ReplaceTest extends TestCase
{
    public function testReplace(): void
    {
        $declaration = new ClassDeclaration('MyClass');
        $declaration->setExtends('Record');

        $declaration->property('names')
            ->setAccess(Partial\Property::ACCESS_PRIVATE)
            ->setComment(['This is foxes', '', '@var array'])
            ->setDefaultValue(['name' => 11, 'value' => 'hi', 'test' => []]);

        $method = $declaration->method('sample');
        $method->parameter('input')->setType('int');
        $method->parameter('output')->setType('int')->setDefaultValue(null)->setPBR(true);
        $method->setAccess(Partial\Method::ACCESS_PUBLIC)->setStatic(true);
        $method->setComment('Get some foxes');

        $method->setSource([
            '$output = $input;',
            'return true;'
        ]);

        $declaration->addTrait('Spiral\Debug\Traits\LoggerTrait');
        $this->assertTrue($declaration->hasTrait('Spiral\Debug\Traits\LoggerTrait'));

        $namespace = new NamespaceDeclaration('Namespace');
        $namespace->setComment('All about foxes');

        $namespace->addElement($declaration);

        $file = new FileDeclaration();
        $file->getComment()->addLine('Full file of foxes');
        $file->addElement($namespace);

        $this->assertSame(
            preg_replace('/\s+/', '', '<?php
            /**
             * Full file of foxes
             */
            /**
             * All about foxes
             */
            namespace Namespace {
                class MyClass extends Record
                {
                    use Spiral\Debug\Traits\LoggerTrait;

                    /**
                     * This is foxes
                     *
                     * @var array
                     */
                    private $names = [
                        \'name\'  => 11,
                        \'value\' => \'hi\',
                        \'test\'  => []
                    ];

                    /**
                     * Get some foxes
                     */
                    public static function sample(int $input, int &$output = null)
                    {
                        $output = $input;
                        return true;
                    }
                }
            }'),
            preg_replace('/\s+/', '', $file->render())
        );

        $file->replace('foxes', 'dogs');

        $this->assertSame(
            preg_replace('/\s+/', '', '<?php
                /**
                 * Full file of dogs
                 */
                /**
                 * All about dogs
                 */
                namespace Namespace {
                    class MyClass extends Record
                    {
                        use Spiral\Debug\Traits\LoggerTrait;

                        /**
                         * This is dogs
                         *
                         * @var array
                         */
                        private $names = [
                            \'name\'  => 11,
                            \'value\' => \'hi\',
                            \'test\'  => []
                        ];

                        /**
                         * Get some dogs
                         */
                        public static function sample(int $input, int &$output = null)
                        {
                            $output = $input;
                            return true;
                        }
                    }
                }'),
            preg_replace(
                '/\s+/',
                '',
                $file->render()
            )
        );
    }
}
