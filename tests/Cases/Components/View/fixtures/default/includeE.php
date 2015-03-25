<alias path="default:extendC" as="myTag"/>

<block:blockA>Block A defined in file include E(default).</block:blockA>
<myTag blockC="Block C defined in include E(default) via attribute.">
    <block:blockA>Block A defined in include E(default) via block.</block:blockA>
</myTag>
<block:blockB>Block B defined in file include E(default).</block:blockB>