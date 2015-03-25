<namespace path="self:includes" name="includes"/>
<alias path="self:includes/blockA" as="aliasA"/>

<block:blockA>Block A defined in file alias A(default).</block:blockA>

<block:blockB>
    Block B defined in file alias A(default).
    <aliasA/>
    <includes:blockB>
        Block B context provided from alias A(default).
    </includes:blockB>
</block:blockB>