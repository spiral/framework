<extend:aliasA/>
<use view="includes/blockC" as="aliasA"/>

<block:blockA>Block A defined in file alias F(default).</block:blockA>

<block:blockB>
    Block B defined in file alias F(default).
    <:aliasE>
        <block:blockA>
            <aliasA>
                Context provided by alias F(default).
            </aliasA>
        </block:blockA>
    </:aliasE>
</block:blockB>