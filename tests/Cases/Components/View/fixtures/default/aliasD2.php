<extends:aliasB/>
<namespace path="self:includes" name="includes"/>

<block:blockA>
    Block A defined in file alias D2(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias D2(default).
        <aliasA/>
    </includes:blockB>
</block:blockA>