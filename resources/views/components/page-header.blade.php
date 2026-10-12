@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'page-toolbar']) }}>
    <div class="page-toolbar__intro">
        <h1 class="page-heading">{{ $title }}</h1>
        @if($subtitle)
        <p class="page-sub">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
    <div class="page-actions">{{ $actions }}</div>
    @endisset
</div>
