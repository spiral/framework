<extends:aliasA/>
<use view="namespace:includes/blockA" as="aliasA"/>

<block:blockB>
    <block:blockB/>
    Block B defined in file alias E(default).
    <aliasA>
        Context provided by alias E(default).
        <i:blockB/>
    </aliasA>
</block:blockB>