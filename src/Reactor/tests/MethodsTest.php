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
use Spiral\Reactor\Aggregator\Methods;
use Spiral\Reactor\Partial;

class MethodsTest extends TestCase
{
    /**
     * @dataProvider staticMethodDataProvider
     * @param bool   $static
     * @param string $body
     */
    public function testStatic(bool $static, string $body): void
    {
        $methods = new Methods([]);
        $method = $methods->get('sample');
        $method->parameter('input')->setType('int');
        $method->parameter('output')->setType('int')->setDefaultValue(null)->setPBR(true);
        $method->setAccess(Partial\Method::ACCESS_PUBLIC)->setStatic($static);

        $method->setSource([
            '$output = $input;',
            'return true;'
        ]);

        $this->assertSame(
            preg_replace('/\s+/', '', $body),
            preg_replace('/\s+/', '', $method->render())
        );
    }

    public function staticMethodDataProvider(): array
    {
        $body = 'public static function sample(int $input, int &$output = null)
                {
                    $output = $input;
                    return true;
                }';

        return [
            [true, $body],
            [false, str_replace('static function', 'function', $body)],
        ];
    }

    /**
     * @dataProvider returnMethodDataProvider
     * @param string $returnType
     * @param string $body
     */
    public function testReturnType(string $returnType, string $body): void
    {
        $methods = new Methods([]);
        $method = $methods->get('sample');
        $method->parameter('input')->setType('int');
        $method->parameter('output')->setType('int')->setDefaultValue(null)->setPBR(true);
        $method->setAccess(Partial\Method::ACCESS_PUBLIC);
        $method->setReturn($returnType);

        $method->setSource([
            '$output = $input;',
            'return true;'
        ]);

        $this->assertSame(
            preg_replace('/\s+/', '', $body),
            preg_replace('/\s+/', '', $method->render())
        );
    }

    public function returnMethodDataProvider(): array
    {
        return [
            [
                '',
                'public function sample(int $input, int &$output = null)
                {
                    $output = $input;
                    return true;
                }'
            ],
            [
                'void',
                'public function sample(int $input, int &$output = null): void
                {
                    $output = $input;
                    return true;
                }'
            ],
        ];
    }
}
