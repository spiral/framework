<use view="includes/blockA" namespace="namespace" as="aliasA"/>
<use view="namespace:includes/*" prefix="i"/>

<block:blockA>Block A defined in file alias B(default).</block:blockA>

<block:blockB>
    Block B defined in file alias B(default).
    <aliasA/>
    <i:blockB>
        Block B context provided from alias B(default).
    </i:blockB>
</block:blockB>