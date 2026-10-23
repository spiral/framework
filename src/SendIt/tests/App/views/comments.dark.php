<extends:sendit:builder subject="Comments"/>
<use:bundle path="sendit:bundle"/>

<block:html>
<!--    <h1>commented</h1>-->
<!--
    multiline {{ $name }}
-->
    <h1>Hello, {{ $name }}!</h1>
<!--[if mso]><b>outlook</b><![endif]-->
<!--[if !mso]><!--><div>not outlook</div><!--<![endif]-->
</block:html>

<block:text>
Hello <!-- not html -->
</block:text>
