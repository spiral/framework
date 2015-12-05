<extends:includes-a value="ABC"/>
<use path="includes/tag-b" as="tagA"/>

<block:e>
    <tagA name="${value}">
        <block:b/>
    </tagA>
</block:e>