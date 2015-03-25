<namespace path="self" name="inner"/>
<block:blockA>Block A defined in file cross A(default).</block:blockA>

<block:blockM>
    <inner:includes.blockA>
        <inner:includes.blockB>
            <inner:includes.blockC/>
        </inner:includes.blockB>
    </inner:includes.blockA>
</block:blockM>

<block:blockB>Block B defined in file cross A(default).</block:blockB>

<block:footer/>