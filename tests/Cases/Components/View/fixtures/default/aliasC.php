<extends:aliasA/>
<namespace path="namespace:includes" name="includes"/>

<block:blockA>
    Block A defined in file alias C(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias C(default).
    </includes:blockB>
</block:blockA>