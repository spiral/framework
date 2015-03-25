<extends:aliasB/>
<alias path="self:includes/blockA" as="aliasA"/>

<block:blockA>
    Block A defined in file alias D3(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias D3(default).
        <aliasA/>
    </includes:blockB>
</block:blockA>