<dark:use path="includes/tag-a" as="tagA"/>

<block:a>Include A, block A.</block:a>
<block:b>
    <tagA name="tag-a">
        <block:c>Include A, block B (inside tag).</block:c>
    </tagA>
</block:b>
<block:e>Include A, block C.</block:e>