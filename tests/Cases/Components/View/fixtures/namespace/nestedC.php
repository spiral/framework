<alias path="default:includes/blockB" as="blockV"/>
<namespace path="self:includes" name="i"/>
This is nested B(namespace).

<i:blockA>
    <i:blockA></i:blockA>
    <blockV>
        <i:blockA/>
    </blockV>
    <i:blockA></i:blockA>
</i:blockA>