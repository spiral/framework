<namespace path="namespace"/>
<namespace path="self" name="inner"/>
<block:blockA>Block A defined in file cross D(default).</block:blockA>

<namespace:crossA>

    <block:blockM>
        Block M prepended by file cross D(default).
        <block:blockM/>
        Block M appended by file cross D(default).
        <inner:includes.blockB>
            Block B context provided by cross D(default).
        </inner:includes.blockB>
    </block:blockM>

    <block:footer>
        <inner:includes.blockB/>
    </block:footer>
</namespace:crossA>

<block:blockB>Block B defined in file cross D(default).</block:blockB>