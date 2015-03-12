<block:blockA>Block A defined in file cross D(namespace).</block:blockA>

<default:crossA blockB="Block B defined in cross D(namespace) via attribute.">
    <block:blockM>
        Block M prepended by file cross D(namespace).
        <block:blockM/>
        Block M appended by file cross D(namespace).
        <includes.blockB>
            Block B context provided by cross D(namespace).
        </includes.blockB>
    </block:blockM>

    <block:footer>
        <includes.blockB/>
    </block:footer>
</default:crossA>

<block:blockB>Block B defined in file cross D(namespace).</block:blockB>