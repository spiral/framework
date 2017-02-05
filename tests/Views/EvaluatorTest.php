<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class EvaluatorTest extends BaseTest
{
    public function testEvaluate()
    {
        $result = $this->views->render('run', [
            'value' => 'abc'
        ]);

        $this->assertSame(
            preg_replace('/\s+/', '','
            <label class="item-form" id="123">
                <span class=" item-label">Hello</span>
                <input type="text" name="" value="abc" data-prefix="" data-pattern="" class="item-input"/>
            </label>
            '),
            preg_replace('/\s+/', '',$result)
        );
    }
}