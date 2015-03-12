<extends:aliasA/>
<use view="namespace:includes/blockA" as="aliasA"/>

<block:blockA>
    Block A defined in file alias D1(default).
    <aliasA/>
    <i.blockB>
        Block B context provided from alias D1(default).
        <aliasA/>
    </i.blockB>
</block:blockA>