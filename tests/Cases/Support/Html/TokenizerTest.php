<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Support\Html;

use Spiral\Components\Tokenizer\Isolator;
use Spiral\Support\Html\Tokenizer;
use Spiral\Support\Tests\TestCase;

class TokenizerTest extends TestCase
{
    public function testInput()
    {
        $tokenizer = new Tokenizer(true, true, new Isolator());

        $tokens = $tokenizer->openFile($filename = __DIR__ . '/fixtures/htmlSample.php');
        $this->assertNotEmpty($tokens);

        $source = file_get_contents($filename);
        $this->assertSame($tokens, $tokenizer->parse($source));
    }

    public function testSerialization()
    {
        $tokenizer = new Tokenizer(true, true, new Isolator());

        $tokens = $tokenizer->openFile($filename = __DIR__ . '/fixtures/htmlSample.php');
        $this->assertNotEmpty($tokens);

        $result = '';
        foreach ($tokens as $token)
        {
            $result .= $token[Tokenizer::TOKEN_CONTENT];
        }

        $this->assertSame(file_get_contents($filename), $result);
    }

    public function testPersistent()
    {
        $tokenizer = new Tokenizer(true, true, new Isolator());

        $tokens = $tokenizer->openFile($filename = __DIR__ . '/fixtures/htmlSample.php');
        $this->assertNotEmpty($tokens);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'html',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<html>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[1]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'body',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<body>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[3]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'div',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<div style="background-color:black; color:white; margin:20px; padding:20px;">',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'style' => 'background-color:black; color:white; margin:20px; padding:20px;',
            )
        ), $tokens[5]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'h2',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<h2>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[7]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'h2',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</h2>',
        ), $tokens[9]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[11]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[13]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p style="<?= "color: yellow" ?>">',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'style' => '<?= "color: yellow" ?>'
            )
        ), $tokens[15]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[17]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[19]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'span',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<span style="color: red;">',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'style' => 'color: red;',
            )
        ), $tokens[21]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'span',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</span>',
        ), $tokens[23]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[25]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p style="<%=ASP CODE%>">',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'style' => '<%=ASP CODE%>',
            )
        ), $tokens[27]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'namespace:span',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<namespace:span <?= \'style="color: red"\' ?>>',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                '<?= \'style="color: red"\' ?>' => ''
            )
        ), $tokens[29]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'namespace:span',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</namespace:span>',
        ), $tokens[33]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[35]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[37]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'img',
            Tokenizer::TOKEN_TYPE       => 'short',
            Tokenizer::TOKEN_CONTENT    => '<img src="http://url" alt="<DEMO> \'IMAGE\'"/>',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'src' => 'http://url',
                'alt' => '<DEMO> \'IMAGE\'',
            )
        ), $tokens[39]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[41]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'p',
            Tokenizer::TOKEN_TYPE       => 'open',
            Tokenizer::TOKEN_CONTENT    => '<p>',
            Tokenizer::TOKEN_ATTRIBUTES => array()
        ), $tokens[43]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME       => 'input',
            Tokenizer::TOKEN_TYPE       => 'short',
            Tokenizer::TOKEN_CONTENT    => '<input type="checkbox" disabled prefix:attribute="ABC"/>',
            Tokenizer::TOKEN_ATTRIBUTES => array(
                'type'             => 'checkbox',
                'disabled'         => '',
                'prefix:attribute' => 'ABC',
            )
        ), $tokens[45]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'p',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</p>',
        ), $tokens[47]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'div',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</div>',
        ), $tokens[49]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'body',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</body>',
        ), $tokens[51]);

        $this->assertSame(array(
            Tokenizer::TOKEN_NAME    => 'html',
            Tokenizer::TOKEN_TYPE    => 'close',
            Tokenizer::TOKEN_CONTENT => '</html>',
        ), $tokens[53]);
    }
}