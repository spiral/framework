<use:element path="import/loop" as="div:loop"/>

<div:loop source="{{ ['a', 'b', 'c'] }}" as="{{ $value }}">
    <b>{{ $value }}</b>
</div:loop>