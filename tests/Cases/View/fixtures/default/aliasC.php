<extends:aliasA/>
<use view="namespace:includes/*" prefix="i."/>

<block:blockA>
    Block A defined in file alias C(default).
    <aliasA/>
    <i.blockB>
        Block B context provided from alias C(default).
    </i.blockB>
</block:blockA>