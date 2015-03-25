<extends:real.realA title="Real C Title."/>
<alias path="namespace:tags/span" as="span"/>
<alias path="namespace:tags/link" as="a"/>
<alias path="self:includes/blockC" as="i"/>
<alias path="self:includes/blockB" as="b"/>

<block:content>
    <block:content/>
    <span>This is real C content.</span>
    <a href="/" target="_blank">This is link in <span>real B</span>.</a>
    <b>
        <b>
            <i>Inside block C (default)</i>
        </b>
    </b>
</block:content>