<use namespace="namespace"/>
<block:blockA>Block A defined in file cross C1(default).</block:blockA>

<namespace:crossA>

    <block:blockM>
        Block M prepended by file cross C1(default).
        <block:blockM/>
    </block:blockM>

    <block:footer>
        <includes.blockB/>
    </block:footer>

</namespace:crossA>

<block:blockB>Block B defined in file cross C1(default).</block:blockB>