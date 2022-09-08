<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Directive\JsonDirective;

class JsonTest extends BaseTest
{
    protected const DIRECTIVES = [
        JsonDirective::class
    ];

    public function testJson(): void
    {
        $doc = $this->parse('@json(["abc"])');

        $this->assertSame(
            '<?php echo json_encode(["abc"], 15, 512) ?>',
            $this->compile($doc)
        );
    }

    public function testJsonOption(): void
    {
        $doc = $this->parse('@json(["abc"], JSON_HEX_TAG)');

        $this->assertSame(
            '<?php echo json_encode(["abc"], JSON_HEX_TAG, 512) ?>',
            $this->compile($doc)
        );
    }

    public function testJsonOptionAndDepth(): void
    {
        $doc = $this->parse('@json(["abc"], JSON_HEX_TAG, 256)');

        $this->assertSame(
            '<?php echo json_encode(["abc"], JSON_HEX_TAG, 256) ?>',
            $this->compile($doc)
        );
    }

    public function testJsonOptionAndDepthButCommas(): void
    {
        $doc = $this->parse('@json(["abc", "cde"], JSON_HEX_TAG, 256)');

        $this->assertSame(
            '<?php echo json_encode(["abc", "cde"], JSON_HEX_TAG, 256) ?>',
            $this->compile($doc)
        );
    }
}
