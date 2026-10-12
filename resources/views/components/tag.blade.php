@props(['variant' => 'muted', 'size' => null])

@php
    $classes = 'tag tag--' . $variant;
    if ($size === 'xs') $classes .= ' tag--xs';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
