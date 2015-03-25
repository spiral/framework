<namespace path="default" name="love"/>
<namespace path="self" name="inner"/>

<block:blockA>Block A defined in file cross D(namespace).</block:blockA>

<love:crossA blockB="Block B defined in cross D(namespace) via attribute.">
    <block:blockM>
        Block M prepended by file cross D(namespace).
        <block:blockM/>
        Block M appended by file cross D(namespace).
        <inner:includes.blockB>
            Block B context provided by cross D(namespace).
        </inner:includes.blockB>
    </block:blockM>

    <block:footer>
        <inner:includes.blockB/>
    </block:footer>
</love:crossA>

<block:blockB>Block B defined in file cross D(namespace).</block:blockB>