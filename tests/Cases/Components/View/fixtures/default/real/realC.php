<extends:real.realA title="Real C Title."/>
<use view="namespace:tags/span" as="span"/>
<use view="namespace:tags/link" as="a"/>
<use view="includes/blockC" as="i"/>
<use view="includes/blockB" as="b"/>

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