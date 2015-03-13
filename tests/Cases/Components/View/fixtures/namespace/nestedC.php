<use view="default:includes/blockB" as="blockV"/>
<use view="includes/*" prefix="i"/>
This is nested B(namespace).

<i:blockA>
    <i:blockA></i:blockA>
    <blockV>
        <i:blockA/>
    </blockV>
    <i:blockA></i:blockA>
</i:blockA>