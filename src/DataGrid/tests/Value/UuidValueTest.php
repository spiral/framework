<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Value;

use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\Value\UuidValue;

class UuidValueTest extends TestCase
{
    /**
     * @dataProvider maskProvider
     * @param string      $mask
     * @param string|null $expectedException
     */
    public function testMask(?string $mask, ?string $expectedException): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $uuid = $mask ? new UuidValue($mask) : new UuidValue();

        $this->assertNotNull($uuid);
    }

    /**
     * @return iterable
     */
    public function maskProvider(): iterable
    {
        return [
            [null, null],
            ['valid', null],
            ['invalid', ValueException::class],
            ['nil', null],
            ['v1', null],
            ['v2', null],
            ['v3', null],
            ['v4', null],
            ['v5', null],
            ['v6', ValueException::class],
        ];
    }

    /**
     * @dataProvider validProvider
     * @param string|null $mask
     * @param string      $value
     * @param bool        $expected
     */
    public function testAccepts(?string $mask, string $value, bool $expected): void
    {
        $uuid = $mask ? new UuidValue($mask) : new UuidValue();
        $this->assertSame($expected, $uuid->accepts($value));
        $this->assertIsString($uuid->convert($value));
    }

    /**
     * @return iterable
     * @throws Exception
     */
    public function validProvider(): iterable
    {
        $masks = ['valid', 'nil', 'v1', 'v2', 'v3', 'v4', 'v5'];
        $random = bin2hex(random_bytes(10));
        foreach ($masks as $mask) {
            yield from [
                [$mask, $random, false],
                [null, $random, false],
            ];
        }

        $ns = '12345678-1234-1234-1234-1234567890ab';
        $nil = Uuid::NIL;
        $uuid1 = Uuid::uuid1()->toString();
        $uuid3 = Uuid::uuid3($ns, 'name')->toString();
        $uuid4 = Uuid::uuid4()->toString();
        $uuid5 = Uuid::uuid5($ns, 'name')->toString();
        $uuids = [$nil, $uuid1, $uuid3, $uuid4, $uuid5];

        foreach ($uuids as $uuid) {
            yield from[
                ['valid', $uuid, true],
                [null, $uuid, true],
            ];
        }

        yield from [
            ['nil', $nil, true],
            ['nil', $uuid1, false],
            ['nil', $uuid3, false],
            ['nil', $uuid4, false],
            ['nil', $uuid5, false],
        ];

        yield from [
            ['v1', $nil, false],
            ['v1', $uuid1, true],
            ['v1', $uuid3, false],
            ['v1', $uuid4, false],
            ['v1', $uuid5, false],
        ];

        yield from [
            ['v2', $nil, false],
            ['v2', $uuid1, false],
            ['v2', $uuid3, false],
            ['v2', $uuid4, false],
            ['v2', $uuid5, false],
        ];

        yield from [
            ['v3', $nil, false],
            ['v3', $uuid1, false],
            ['v3', $uuid3, true],
            ['v3', $uuid4, false],
            ['v3', $uuid5, false],
        ];

        yield from [
            ['v4', $nil, false],
            ['v4', $uuid1, false],
            ['v4', $uuid3, false],
            ['v4', $uuid4, true],
            ['v4', $uuid5, false],
        ];

        yield from [
            ['v5', $nil, false],
            ['v5', $uuid1, false],
            ['v5', $uuid3, false],
            ['v5', $uuid4, false],
            ['v5', $uuid5, true],
        ];
    }
}
