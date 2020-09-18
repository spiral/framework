<use:element path="import/loop" as="div:loop"/>

<div:loop source="{{ $input }}" as="{{ $value }}">
    <b>{{ $value }}</b>
</div:loop>