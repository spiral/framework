<namespace path="self:includes" name="includes"/>
<namespace path="namespace"/>

<block:blockA>Block A defined in file cross C2(default).</block:blockA>

<namespace:crossA>

    <block:blockM>
        Block M prepended by file cross C2(default).
        <block:blockM/>
        Block M appended by file cross C2(default).
    </block:blockM>

    <block:footer>
        <includes:blockB/>
    </block:footer>

</namespace:crossA>

<block:blockB>Block B defined in file cross C2(default).</block:blockB>