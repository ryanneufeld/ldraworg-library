@props(['part'])
<div class="text-lg font-bold">Part Events:</div>
<div class="flex flex-col space-y-4">
    @if (!$part->isUnofficial() || !is_null($part->official_part))
        <x-accordion id="archiveEvents">
            <x-slot name="header">
                Archived Part Events:
            </x-slot>
            @forelse ($part->events->whereNotNull('part_release_id')->sortBy('created_at') as $event)
                <x-event.list.item :event="$event"/>
            @empty
                <div>None</div>
            @endforelse 
        </x-accordion>
    @endif
    @if ($part->isUnofficial())
        @forelse ($part->events->whereNull('part_release_id')->sortBy('created_at') as $event)
            <x-event.list.item :event="$event"/>
        @empty
            <div>No Events</div>
        @endforelse
    @endif 
</div>
