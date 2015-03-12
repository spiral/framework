<extends:aliasB/>
<use view="includes/*" prefix="i"/>

<block:blockA>
    Block A defined in file alias D2(default).
    <aliasA/>
    <i:blockB>
        Block B context provided from alias D2(default).
        <aliasA/>
    </i:blockB>
</block:blockA>