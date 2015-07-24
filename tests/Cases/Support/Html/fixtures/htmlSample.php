<!DOCTYPE html>
<html>
<body>
<div style="background-color:black; color:white; margin:20px; padding:20px;">
    <h2>London</h2>

    <p>Testing php usage in attribute values.</p>

    <p style="<?= "color: yellow" ?>">
        London is the capital city of England. It is the most populous city in the United Kingdom,
        with a metropolitan area of over 13 million inhabitants.
    </p>

    <p>
        Standing on the River Thames, London has been a
        <span style="color: red;">major settlement</span>
        for two millennia, its history going back to its founding by the Romans, who named it
        Londinium.
    </p>

    <p style="<%=ASP CODE%>">
        Tags with PHP code:
        <namespace:span <?= 'style="color: red"' ?>>Some >> << data.</namespace:span>
    </p>

    <p>
        Testing short tags and attribute values.
        <img src="http://url" alt="<DEMO> 'IMAGE'"/>
    </p>

    <p>
        Testing attributes without values.
        <input type="checkbox" title="" disabled prefix:attribute="ABC"/>
    </p>
</div>
</body>
</html>