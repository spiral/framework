<use view="includes/blockA" as="aliasA"/>
<use view="includes/*" prefix="i."/>

<block:blockA>Block A defined in file alias A(default).</block:blockA>

<block:blockB>
    Block B defined in file alias A(default).
    <aliasA/>
    <i.blockB>
        Block B context provided from alias A(default).
    </i.blockB>
</block:blockB>