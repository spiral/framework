<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Directive\PHPDirective;

class PHPTest extends BaseTestCase
{
    protected const DIRECTIVES = [
        PHPDirective::class
    ];

    public function testPHP(): void
    {
        $doc = $this->parse('@php echo 1; @endphp');

        $this->assertSame(
            '<?php echo 1; ?>',
            $this->compile($doc)
        );
    }
}
