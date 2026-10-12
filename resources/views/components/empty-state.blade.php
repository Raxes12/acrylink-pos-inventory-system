@props([
    'title' => 'No results found',
    'message' => null,
    'icon' => 'search',
    'colspan' => null,
])

@if($colspan)
<tr>
    <td colspan="{{ $colspan }}">
@endif

<div {{ $attributes->merge(['class' => 'empty-state empty-state--rich']) }}>
    <div class="empty-state__icon" aria-hidden="true">
        <x-icon :name="$icon" />
    </div>
    <p class="empty-state__title">{{ $title }}</p>
    @if($message)
    <p class="empty-state__message">{{ $message }}</p>
    @endif
    @isset($action)
    <div class="empty-state__action">{{ $action }}</div>
    @endisset
</div>

@if($colspan)
    </td>
</tr>
@endif
