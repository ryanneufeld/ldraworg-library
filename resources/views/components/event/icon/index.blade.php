@props(['event'])
@php
    $is_fix = $event->initial_submit && !is_null($event->part->official_part);
    $stack = (!is_null($event->comment) && $event->part_event_type->slug != 'comment') || $is_fix;
@endphp
<div class="relative w-6">
@switch($event->part_event_type->slug)
    @case('submit')
        <x-fas-file class="fill-black" />
        @break
    @case('review')
        @switch($event->vote_type_code)
            @case('A')
            @case('T')
                <x-fas-check class="fill-lime-400" />
                @break
            @case('C')
                <x-fas-check class="fill-green-600" />
                @break
            @case('H')
                <x-fas-circle-exclamation class="fill-red-600" />
                @break
            @default
                <x-fas-undo class="fill-black" />
        @endswitch
        @break
    @case('comment')
        <x-fas-comment class="fill-blue-500" />
        @break
    @case('edit')
        <x-fas-edit class="fill-black" />
        @break
    @case('rename')
        <x-fas-file-export class="fill-black" />
        @break
    @case('release')
        <x-fas-graduation-cap class="fill-green-600" />
        @break
    @case('delete')
        <x-fas-recycle class="fill-black" />
        @break
    @default
        <x-fas-circle class="fill-blue-500" />
@endswitch 
@if($stack)
    @if(!is_null($event->comment))
        <x-fas-comment class="absolute bottom-0 left-0 w-1/2 fill-blue-500" />
    @endif
    @if($is_fix)
        <x-fas-tools class="absolute bottom-0 right-0 w-1/2 fill-green-600" />
    @endif
@endif
</div>
