<namespace path="self"/>

<block:blockA>Block A defined in file cross A(namespace).</block:blockA>
<block:blockM>
    <self:includes.blockA>
        <self:includes.blockB>
            Context provided by cross A(namespace).
        </self:includes.blockB>
    </self:includes.blockA>
</block:blockM>
<block:blockB>Block B defined in file cross A(namespace).</block:blockB>
<block:footer/>