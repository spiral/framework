<use path="namespace:includes/tag-c" as="tagA"/>
<use path="default:includes/tag-b" as="tagB"/>

<tagA class="my-class" id="123">
    <tagB name="tag-b">
        <tagA class="new-class" value="abc">
            Some context.
        </tagA>
    </tagB>
</tagA>