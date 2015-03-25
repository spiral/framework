<extends:aliasA/>
<namespace path="namespace:includes" name="includes"/>

<block:blockA>
    Block A defined in file alias D(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias D(default).
        <aliasA/>
    </includes:blockB>
</block:blockA>