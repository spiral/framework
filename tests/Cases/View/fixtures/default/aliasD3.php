<extends:aliasB/>
<use view="includes/blockA" as="aliasA"/>

<block:blockA>
    Block A defined in file alias D3(default).
    <aliasA/>
    <i.blockB>
        Block B context provided from alias D3(default).
        <aliasA/>
    </i.blockB>
</block:blockA>