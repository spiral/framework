<extends:includes-a/>
<use path="includes/tag-b" as="tagA"/>

<block:c>
    <tagA name="tag-b">
        Include A, block C (inside tag B).
    </tagA>
</block:c>