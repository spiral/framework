<extends:aliasA/>
<alias path="namespace:includes/blockA" as="aliasA"/>

<block:blockB>
    <block:blockB/>
    Block B defined in file alias E(default).
    <aliasA>
        Context provided by alias E(default).
        <includes:blockB/>
    </aliasA>
</block:blockB>