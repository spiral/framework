<extends:aliasA/>
<alias path="namespace:includes/blockA" as="aliasA"/>

<block:blockA>
    Block A defined in file alias D1(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias D1(default).
        <aliasA/>
    </includes:blockB>
</block:blockA>