<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Directive\ConditionalDirective;

class ConditionalTest extends BaseTest
{
    protected const DIRECTIVES = [
        ConditionalDirective::class
    ];

    public function testIfEndif(): void
    {
        $doc = $this->parse('@if(true) ok @endif');

        $this->assertSame(
            '<?php if(true): ?> ok <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testIfElseEndif(): void
    {
        $doc = $this->parse('@if(true) ok @else bad @endif');

        $this->assertSame(
            '<?php if(true): ?> ok <?php else: ?> bad <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testIfElseifElseEndif(): void
    {
        $doc = $this->parse('@if(true) ok @elseif(true) other @else bad @endif');

        $this->assertSame(
            '<?php if(true): ?> ok <?php elseif(true): ?> other <?php else: ?> bad <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testUnless(): void
    {
        $doc = $this->parse('@unless(false) ok @endunless');

        $this->assertSame(
            '<?php if(!(false)): ?> ok <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testIsset(): void
    {
        $doc = $this->parse('@isset($var) ok @endisset');

        $this->assertSame(
            '<?php if(isset($var)): ?> ok <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testEmpty(): void
    {
        $doc = $this->parse('@empty($var) ok @endempty');

        $this->assertSame(
            '<?php if(empty($var)): ?> ok <?php endif; ?>',
            $this->compile($doc)
        );
    }

    public function testSwitchCase(): void
    {
        $doc = $this->parse('@switch(1) @case(1) 1 @endswitch');

        $this->assertSame(
            '<?php switch(1): case (1): ?> 1 <?php endswitch; ?>',
            $this->compile($doc)
        );
    }

    public function testSwitchCaseDefaultBreak(): void
    {
        $doc = $this->parse('@switch($var) @case(1) 1 @break @default default @endswitch');

        $this->assertSame(
            '<?php switch($var): case (1): ?> 1 <?php break; ?> <?php default: ?> default <?php endswitch; ?>',
            $this->compile($doc)
        );
    }
}
