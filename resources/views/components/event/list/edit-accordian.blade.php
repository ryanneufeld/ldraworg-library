@props(['changes'])
<x-accordion id="officialParts">
    <x-slot name="header">
        Header Edits
    </x-slot>
    <p>
        @foreach($changes['old'] as $field => $value)
            {{$field}}:<br> 
            <code class="break-words font-mono">{!! nl2br($value) !!}</code><br>
            to<br>
            <code class="break-words font-mono">{!! nl2br($changes['new'][$field]) !!}</code><br>
        @endforeach
    </p>
</x-accordion>