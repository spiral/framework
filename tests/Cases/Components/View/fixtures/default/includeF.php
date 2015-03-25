<namespace path="default"/>
<namespace path="namespace"/>

<block:blockA>Block A defined in file include F(default).</block:blockA>
<default:extendC>
    <block:blockB>
        Importing extendC from namespace.
        <namespace:extendC blockC="Block C of namespace:extendC defined in file include F(default) via attribute."/>
    </block:blockB>
</default:extendC>
<block:blockB>Block B defined in file include F(default).</block:blockB>