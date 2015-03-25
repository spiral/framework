<namespace path="namespace:includes" name="includes"/>
<alias path="namespace:includes/blockA" as="aliasA"/>

<block:blockA>Block A defined in file alias B(default).</block:blockA>

<block:blockB>
    Block B defined in file alias B(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias B(default).
    </includes:blockB>
</block:blockB>