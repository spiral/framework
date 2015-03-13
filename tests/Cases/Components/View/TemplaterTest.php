<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\View;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\View;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class TemplaterTest extends TestCase
{
    public function testExtendA()
    {
        //Direct template call
        $result = $this->render('extendA');

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file A(default).', $result[2]);
    }

    public function testExtendB()
    {
        //Simple extending
        $result = $this->render('extendB');

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file B(default).', $result[1]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[2]);
    }

    public function testExtendC()
    {
        //Double extending
        $result = $this->render('extendC');

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file C(default).', $result[1]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[2]);
    }

    public function testExtendD()
    {
        //Extending with parent block
        $result = $this->render('extendD');
        $result = array_map('trim', $result);

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file B(default).', $result[1]);
        $this->assertSame('Block B appended in file D(default).', $result[2]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[3]);
    }

    public function testExtendE()
    {
        //Extending with parent block
        $result = $this->render('extendE');
        $result = array_map('trim', $result);

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B prepended in file E(default).', $result[1]);
        $this->assertSame('Block B defined in file B(default).', $result[2]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[3]);
    }

    public function testExtendF()
    {
        //Extending with parent block
        $result = $this->render('extendF');
        $result = array_map('trim', $result);

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B prepended in file F(default).', $result[1]);
        $this->assertSame('Block B defined in file B(default).', $result[2]);
        $this->assertSame('Block B appended in file F(default).', $result[3]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[4]);
    }

    public function testNamespaceExtendA()
    {
        //Simple extending (namespace)
        $result = $this->render('namespace:extendA');

        $this->assertSame('Block A defined in file A(namespace).', $result[0]);
        $this->assertSame('Block B defined in file A(namespace).', $result[1]);
        $this->assertSame('Block C defined in file A(namespace).', $result[2]);
    }

    public function testNamespaceExtendB()
    {
        //Direct template call (namespace)
        $result = $this->render('namespace:extendB');

        $this->assertSame('Block A defined in file A(namespace).', $result[0]);
        $this->assertSame('Block B defined in file B(namespace).', $result[1]);
        $this->assertSame('Block C define in file B(namespace), using attribute.', $result[2]);
    }

    public function testNamespaceExtendC()
    {
        //Double extending (namespace)
        $result = $this->render('namespace:extendC');

        $this->assertSame('Block A defined in file A(namespace).', $result[0]);
        $this->assertSame('Block B defined in file C(namespace).', $result[1]);
        $this->assertSame('Block C define in file B(namespace), using attribute.', $result[2]);
    }

    public function testIncludeA()
    {
        //Testing simple importing, some block has same names in both parent and child
        //As block has simple name we have to declare explicitly
        $result = $this->render('includeA');

        $this->assertSame('Block A defined in file include A(default).', $result[0]);
        $this->assertSame('Block A defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file A(default).', $result[2]);
        $this->assertSame('Block C defined in file A(default).', $result[3]);
        $this->assertSame('Block B defined in file include A(default).', $result[4]);
    }

    public function testIncludeB()
    {
        //Include extended template
        //We using longer declaration
        $result = $this->render('includeB');

        $this->assertSame('Block A defined in file include B(default).', $result[0]);
        $this->assertSame('Block A defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file B(default).', $result[2]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[3]);
        $this->assertSame('Block B defined in file include B(default).', $result[4]);
    }

    public function testIncludeC()
    {
        //Should be identical to namespace:extendC
        //Including whole namespace
        $result = $this->render('includeC');

        $this->assertSame('Block A defined in file include C(default).', $result[0]);
        $this->assertSame('Block A defined in file A(namespace).', $result[1]);
        $this->assertSame('Block B defined in file C(namespace).', $result[2]);
        $this->assertSame('Block C define in file B(namespace), using attribute.', $result[3]);
        $this->assertSame('Block B defined in file include C(default).', $result[4]);
    }

    public function testIncludeD()
    {
        //Should be identical to namespace:extendC
        //Custom prefix
        $result = $this->render('includeD');

        $this->assertSame('Block A defined in file include D(default).', $result[0]);
        $this->assertSame('Block A defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file C(default).', $result[2]);
        $this->assertSame('Block C define in file B(default), using attribute.', $result[3]);
        $this->assertSame('Block B defined in file include D(default).', $result[4]);
    }

    public function testIncludeE()
    {
        //Include with extending
        //Alias + some blocks should be rewritten
        $result = $this->render('includeE');

        $this->assertSame('Block A defined in file include E(default).', $result[0]);
        $this->assertSame('Block A defined in include E(default) via block.', $result[1]);
        $this->assertSame('Block B defined in file C(default).', $result[2]);
        $this->assertSame('Block C defined in include E(default) via attribute.', $result[3]);
        $this->assertSame('Block B defined in file include E(default).', $result[4]);
    }

    public function testIncludeF()
    {
        //Simple include inside include
        $result = $this->render('includeF');

        $this->assertSame('Block A defined in file include F(default).', $result[0]);

        //First include
        $this->assertSame('Block A defined in file A(default).', $result[1]);
        $this->assertSame('Importing extendC from namespace.', $result[2]);

        //Inner include, should be equal to namespace:extendC
        $this->assertSame('Block A defined in file A(namespace).', $result[3]);
        $this->assertSame('Block B defined in file C(namespace).', $result[4]);
        $this->assertSame('Block C of namespace:extendC defined in file include F(default) via attribute.', $result[5]);
        //End of inner include

        $this->assertSame('Block C define in file B(default), using attribute.', $result[6]);
        //End of first include

        $this->assertSame('Block B defined in file include F(default).', $result[7]);
    }

    public function testIncludeG()
    {
        //Automatically load tag with . included (same namespace)
        $result = $this->render('includeG');

        //Block A (default) expected
        $this->assertSame('Block A defined in file include G(default).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('Context provided by include G(default).', $result[2]);
        $this->assertSame('Block B defined in file include G(default).', $result[3]);
    }

    public function testNamespaceIncludeG()
    {
        //Automatically load tag with . included (same namespace)
        $result = $this->render('namespace:includeG');

        //Block A (default) expected
        $this->assertSame('Block A defined in file include G(namespace).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('Context provided by include G(namespace).', $result[2]);
        $this->assertSame('Block B defined in file include G(namespace).', $result[3]);
    }

    public function testCrossA()
    {
        //Cross imports, making sure namespace not lost
        $result = $this->render('crossA');

        //Blocks should be included from default namespace
        $this->assertSame('Block A defined in file cross A(default).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('This is block B(default).', $result[2]);
        $this->assertSame('This is block C(default). Exists only under default.', $result[3]);
        $this->assertSame('Block B defined in file cross A(default).', $result[4]);
    }

    public function testNamespaceCrossA()
    {
        //Checking namespace
        $result = $this->render('namespace:crossA');

        //All imports should be from "namespace"
        $this->assertSame('Block A defined in file cross A(namespace).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('This is block B(namespace).', $result[2]);
        $this->assertSame('Context provided by cross A(namespace).', $result[3]);
        $this->assertSame('Block B defined in file cross A(namespace).', $result[4]);
    }

    public function testCrossB()
    {
        //Including element inside another namespace but local context
        $result = $this->render('crossB');

        $this->assertSame('Block A defined in file cross B(default).', $result[0]);
        $this->assertSame('Block A defined in file cross A(namespace).', $result[1]);
        $this->assertSame('This is block A(namespace).', $result[2]);
        $this->assertSame('This is block B(namespace).', $result[3]);
        $this->assertSame('Context provided by cross A(namespace).', $result[4]);
        $this->assertSame('Block B defined in file cross A(namespace).', $result[5]);

        //Block B should be retrieved from default, even including inside context
        $this->assertSame('This is block B(default).', $result[6]);
        $this->assertSame('Block B defined in file cross B(default).', $result[7]);
    }

    public function testNamespaceCrossB()
    {
        //Including element inside another namespace but local context (reverted)
        $result = $this->render('namespace:crossB');

        $this->assertSame('Block A defined in file cross B(namespace).', $result[0]);
        $this->assertSame('Block A defined in file cross A(default).', $result[1]);
        $this->assertSame('This is block A(default).', $result[2]);
        $this->assertSame('This is block B(default).', $result[3]);
        $this->assertSame('This is block C(default). Exists only under default.', $result[4]);
        $this->assertSame('Block B defined in file cross A(default).', $result[5]);

        //Block B should be retrieved from default, even including inside context
        $this->assertSame('This is block B(namespace).', $result[6]);
        $this->assertSame('Block B defined in file cross B(namespace).', $result[7]);
    }

    public function testCrossC()
    {
        //Including element inside another namespace but local context
        //+ parent block values (before extend)
        $result = $this->render("crossC");

        $this->assertSame('Block A defined in file cross C(default).', $result[0]);
        $this->assertSame('Block A defined in file cross A(namespace).', $result[1]);
        $this->assertSame('This is block A(namespace).', $result[2]);
        $this->assertSame('This is block B(namespace).', $result[3]);
        $this->assertSame('Context provided by cross A(namespace).', $result[4]);
        $this->assertSame('Block M appended by file cross C(default).', $result[5]);
        $this->assertSame('Block B defined in file cross A(namespace).', $result[6]);
        $this->assertSame('This is block B(default).', $result[7]);
        $this->assertSame('Block B defined in file cross C(default).', $result[8]);
    }

    public function testCrossC1()
    {
        //Including element inside another namespace but local context
        //+ parent block values (after extend)
        $result = $this->render("crossC1");

        $this->assertSame('Block A defined in file cross C1(default).', $result[0]);
        $this->assertSame('Block A defined in file cross A(namespace).', $result[1]);
        $this->assertSame('Block M prepended by file cross C1(default).', $result[2]);
        $this->assertSame('This is block A(namespace).', $result[3]);
        $this->assertSame('This is block B(namespace).', $result[4]);
        $this->assertSame('Context provided by cross A(namespace).', $result[5]);
        $this->assertSame('Block B defined in file cross A(namespace).', $result[6]);
        $this->assertSame('This is block B(default).', $result[7]);
        $this->assertSame('Block B defined in file cross C1(default).', $result[8]);
    }

    public function testCrossC2()
    {
        //Including element inside another namespace but local context
        //+ parent block values (after and before extend)
        $result = $this->render("crossC2");

        $this->assertSame('Block A defined in file cross C2(default).', $result[0]);
        $this->assertSame('Block A defined in file cross A(namespace).', $result[1]);
        $this->assertSame('Block M prepended by file cross C2(default).', $result[2]);
        $this->assertSame('This is block A(namespace).', $result[3]);
        $this->assertSame('This is block B(namespace).', $result[4]);
        $this->assertSame('Context provided by cross A(namespace).', $result[5]);
        $this->assertSame('Block M appended by file cross C2(default).', $result[6]);

        $this->assertSame('Block B defined in file cross A(namespace).', $result[7]);
        $this->assertSame('This is block B(default).', $result[8]);
        $this->assertSame('Block B defined in file cross C2(default).', $result[9]);
    }

    public function testCrossD()
    {
        //Including element inside another namespace but local context + parent block values (after and before extend) +
        //local import + context
        $result = $this->render("crossD");

        $this->assertSame('Block A defined in file cross D(default).', $result[0]);
        $this->assertSame('Block A defined in file cross A(namespace).', $result[1]);
        $this->assertSame('Block M prepended by file cross D(default).', $result[2]);
        $this->assertSame('This is block A(namespace).', $result[3]);
        $this->assertSame('This is block B(namespace).', $result[4]);
        $this->assertSame('Context provided by cross A(namespace).', $result[5]);
        $this->assertSame('Block M appended by file cross D(default).', $result[6]);

        $this->assertSame('This is block B(default).', $result[7]);
        $this->assertSame('Block B context provided by cross D(default).', $result[8]);

        $this->assertSame('Block B defined in file cross A(namespace).', $result[9]);
        $this->assertSame('This is block B(default).', $result[10]);
        $this->assertSame('Block B defined in file cross D(default).', $result[11]);
    }

    public function testNamespaceCrossD()
    {
        //Including element inside another namespace but local context + parent block values (after and before extend) +
        //local import + context + another namespace + namespace prefix
        $result = $this->render("namespace:crossD");

        $this->assertSame('Block A defined in file cross D(namespace).', $result[0]);
        $this->assertSame('Block A defined in file cross A(default).', $result[1]);
        $this->assertSame('Block M prepended by file cross D(namespace).', $result[2]);
        $this->assertSame('This is block A(default).', $result[3]);
        $this->assertSame('This is block B(default).', $result[4]);
        $this->assertSame('This is block C(default). Exists only under default.', $result[5]);
        $this->assertSame('Block M appended by file cross D(namespace).', $result[6]);
        $this->assertSame('This is block B(namespace).', $result[7]);
        $this->assertSame('Block B context provided by cross D(namespace).', $result[8]);
        $this->assertSame('Block B defined in cross D(namespace) via attribute.', $result[9]);
        $this->assertSame('This is block B(namespace).', $result[10]);
        $this->assertSame('Block B defined in file cross D(namespace).', $result[11]);
    }

    public function testAliasA()
    {
        //Simple aliasing
        $result = $this->render('aliasA');

        $this->assertSame('Block A defined in file alias A(default).', $result[0]);
        $this->assertSame('Block B defined in file alias A(default).', $result[1]);
        $this->assertSame('This is block A(default).', $result[2]);
        $this->assertSame('This is block B(default).', $result[3]);
        $this->assertSame('Block B context provided from alias A(default).', $result[4]);
    }

    public function testAliasB()
    {
        //Aliasing to another namespace
        $result = $this->render("aliasB");

        $this->assertSame('Block A defined in file alias B(default).', $result[0]);
        $this->assertSame('Block B defined in file alias B(default).', $result[1]);
        $this->assertSame('This is block A(namespace).', $result[2]);
        $this->assertSame('This is block B(namespace).', $result[3]);
        $this->assertSame('Block B context provided from alias B(default).', $result[4]);
    }

    public function testAliasC()
    {
        //Extending view with aliases + realising
        $result = $this->render("aliasC");

        $this->assertSame('Block A defined in file alias C(default).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('This is block B(namespace).', $result[2]);
        $this->assertSame('Block B context provided from alias C(default).', $result[3]);
        $this->assertSame('Block B defined in file alias A(default).', $result[4]);
        $this->assertSame('This is block A(default).', $result[5]);
        $this->assertSame('This is block B(default).', $result[6]);
        $this->assertSame('Block B context provided from alias A(default).', $result[7]);
    }

    public function testAliasD()
    {
        //Passing aliases inside include
        $result = $this->render("aliasD");

        $this->assertSame('Block A defined in file alias D(default).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('This is block B(namespace).', $result[2]);
        $this->assertSame('Block B context provided from alias D(default).', $result[3]);
        $this->assertSame('This is block A(default).', $result[4]);
        $this->assertSame('Block B defined in file alias A(default).', $result[5]);
        $this->assertSame('This is block A(default).', $result[6]);
        $this->assertSame('This is block B(default).', $result[7]);
        $this->assertSame('Block B context provided from alias A(default).', $result[8]);
    }

    public function testAliasD1()
    {
        //Passing aliases inside include
        $result = $this->render("aliasD1");

        $this->assertSame('Block A defined in file alias D1(default).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('This is block B(default).', $result[2]);
        $this->assertSame('Block B context provided from alias D1(default).', $result[3]);
        $this->assertSame('This is block A(namespace).', $result[4]);
        $this->assertSame('Block B defined in file alias A(default).', $result[5]);
        $this->assertSame('This is block A(default).', $result[6]);
        $this->assertSame('This is block B(default).', $result[7]);
        $this->assertSame('Block B context provided from alias A(default).', $result[8]);
    }

    public function testAliasD2()
    {
        //Passing aliases inside include + redefinition
        $result = $this->render("aliasD2");

        $this->assertSame('Block A defined in file alias D2(default).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('This is block B(default).', $result[2]);
        $this->assertSame('Block B context provided from alias D2(default).', $result[3]);
        $this->assertSame('This is block A(namespace).', $result[4]);
        $this->assertSame('Block B defined in file alias B(default).', $result[5]);
        $this->assertSame('This is block A(namespace).', $result[6]);
        $this->assertSame('This is block B(namespace).', $result[7]);
        $this->assertSame('Block B context provided from alias B(default).', $result[8]);
    }

    public function testAliasD3()
    {
        //Passing aliases inside include + redefinition
        $result = $this->render("aliasD3");

        $this->assertSame('Block A defined in file alias D3(default).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('This is block B(namespace).', $result[2]);
        $this->assertSame('Block B context provided from alias D3(default).', $result[3]);
        $this->assertSame('This is block A(default).', $result[4]);
        $this->assertSame('Block B defined in file alias B(default).', $result[5]);
        $this->assertSame('This is block A(namespace).', $result[6]);
        $this->assertSame('This is block B(namespace).', $result[7]);
        $this->assertSame('Block B context provided from alias B(default).', $result[8]);
    }

    public function testAliasE()
    {
        //Aliases with parent block
        $result = $this->render("aliasE");

        $this->assertSame('Block A defined in file alias A(default).', $result[0]);
        $this->assertSame('Block B defined in file alias A(default).', $result[1]);
        $this->assertSame('This is block A(default).', $result[2]);
        $this->assertSame('This is block B(default).', $result[3]);
        $this->assertSame('Block B context provided from alias A(default).', $result[4]);
        $this->assertSame('Block B defined in file alias E(default).', $result[5]);
        $this->assertSame('This is block A(namespace).', $result[6]);
        $this->assertSame('Context provided by alias E(default).', $result[7]);
        $this->assertSame('This is block B(default).', $result[8]);
    }

    public function testRealA()
    {
        //Simple "real" example
        $result = $this->render("real/realA");

        $this->assertSame('<html>', $result[0]);
        $this->assertSame('<head>', $result[1]);
        $this->assertSame('<title>Real A Title</title>', $result[2]);
        $this->assertSame('</head>', $result[3]);
        $this->assertSame('<body>', $result[4]);
        $this->assertSame('<span>This is real A body.</span>', $result[5]);
        $this->assertSame('<input type="text" name="input" value="real value A" class="class"/>', $result[6]);
        $this->assertSame('<input type="date" name="date-input" value="real value B"/>', $result[7]);
        $this->assertSame('<input type="password" name="password" value="real value C"/>', $result[8]);
        $this->assertSame('</body>', $result[9]);
        $this->assertSame('</html>', $result[10]);
    }

    public function testRealB()
    {
        //Little bit more complex "real" example
        $result = $this->render("real/realB");

        $this->assertSame('<html>', $result[0]);
        $this->assertSame('<head>', $result[1]);
        $this->assertSame('<title>Real B Title.</title>', $result[2]);
        $this->assertSame('</head>', $result[3]);
        $this->assertSame('<body>', $result[4]);
        $this->assertSame('<span>This is real A body.</span>', $result[5]);
        $this->assertSame('<input type="text" name="input" value="real value A" class="class"/>', $result[6]);
        $this->assertSame('<input type="date" name="date-input" value="real value B"/>', $result[7]);
        $this->assertSame('<input type="password" name="password" value="real value C"/>', $result[8]);
        $this->assertSame('<span class="custom-span">This is real B content.</span>', $result[9]);
        $this->assertSame('<a href="/" class="custom-link" target="_blank">This is link in <span class="custom-span">real B</span>.</a>', $result[10]);
        $this->assertSame('</body>', $result[11]);
        $this->assertSame('</html>', $result[12]);
    }

    public function testRealC()
    {
        //Little more imports
        $result = $this->render("real/realC");

        $this->assertSame('<html>', $result[0]);
        $this->assertSame('<head>', $result[1]);
        $this->assertSame('<title>Real C Title.</title>', $result[2]);
        $this->assertSame('</head>', $result[3]);
        $this->assertSame('<body>', $result[4]);
        $this->assertSame('<span>This is real A body.</span>', $result[5]);
        $this->assertSame('<input type="text" name="input" value="real value A" class="class"/>', $result[6]);
        $this->assertSame('<input type="date" name="date-input" value="real value B"/>', $result[7]);
        $this->assertSame('<input type="password" name="password" value="real value C"/>', $result[8]);
        $this->assertSame('<span class="custom-span">This is real C content.</span>', $result[9]);
        $this->assertSame('<a href="/" class="custom-link" target="_blank">This is link in <span class="custom-span">real B</span>.</a>', $result[10]);
        $this->assertSame('This is block B(default).', $result[11]);
        $this->assertSame('This is block B(default).', $result[12]);
        $this->assertSame('This is block C(default). Exists only under default.', $result[13]);
        $this->assertSame('Inside block C (default)', $result[14]);
        $this->assertSame('</body>', $result[15]);
        $this->assertSame('</html>', $result[16]);
    }

    public function testNamespaceNestedA()
    {
        //Nested blocks
        $result = $this->render("namespace:nestedA");

        $this->assertSame('This is nested B(namespace).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('This is block A(namespace).', $result[2]);
        $this->assertSame('This is block B(namespace).', $result[3]);
        $this->assertSame('This is block A(namespace).', $result[4]);
        $this->assertSame('This is block A(namespace).', $result[5]);
    }

    public function testNamespaceNestedB()
    {
        //Nested blocks
        $result = $this->render("namespace:nestedB");

        $this->assertSame('This is nested B(namespace).', $result[0]);
        $this->assertSame('This is block A(default).', $result[1]);
        $this->assertSame('This is block A(default).', $result[2]);
        $this->assertSame('This is block B(namespace).', $result[3]);
        $this->assertSame('This is block A(default).', $result[4]);
        $this->assertSame('This is block A(default).', $result[5]);
    }

    public function testNamespaceNestedC()
    {
        //Nested blocks
        $result = $this->render("namespace:nestedC");

        $this->assertSame('This is nested B(namespace).', $result[0]);
        $this->assertSame('This is block A(namespace).', $result[1]);
        $this->assertSame('This is block A(namespace).', $result[2]);
        $this->assertSame('This is block B(default).', $result[3]);
        $this->assertSame('This is block A(namespace).', $result[4]);
        $this->assertSame('This is block A(namespace).', $result[5]);
    }

    /**
     * Render view and return it's blank lines.
     *
     * @param string $view
     * @return array
     */
    protected function render($view, $blankLines = false)
    {
        $lines = explode("\n", StringHelper::normalizeEndings($this->viewComponent()->render($view)));

        return array_values(array_map('trim', array_filter($lines, 'trim')));
    }

    /**
     * Configured view component.
     *
     * @param array $config
     * @return View
     * @throws \Spiral\Core\CoreException
     */
    protected function viewComponent(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'namespaces'        => array(
                    'default'   => array(
                        __DIR__ . '/fixtures/default/',
                        __DIR__ . '/fixtures/default-b/',
                    ),
                    'namespace' => array(
                        __DIR__ . '/fixtures/namespace/',
                    )
                ),
                'caching'           => array(
                    'enabled'   => false,
                    'directory' => directory('runtime') . '/'
                ),
                'variableProviders' => array(),
                'processors'        => array(
                    'templater' => array(
                        'class' => 'Spiral\\Components\\View\\Processors\\TemplateProcessor'
                    )
                )
            );
        }

        return new View(
            MemoryCore::getInstance()->setConfig('views', $config),
            new FileManager()
        );
    }
}