<namespace path="default" name="default"/>
<namespace path="self"/>

<block:blockA>Block A defined in file cross B(namespace).</block:blockA>

<default:crossA>
    <block:footer>
        <self:includes.blockB/>
    </block:footer>
</default:crossA>

<block:blockB>Block B defined in file cross B(namespace).</block:blockB>