<use namespace="default"/>
<block:blockA>Block A defined in file cross B(namespace).</block:blockA>

<default:crossA>
    <block:footer>
        <includes.blockB/>
    </block:footer>
</default:crossA>

<block:blockB>Block B defined in file cross B(namespace).</block:blockB>