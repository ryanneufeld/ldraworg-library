@props(['events' => null, 'title' => ''])
<div class="text-lg font-bold">{{$title}}</div>
<div class="flex flex-col space-y-4">
    @forelse ($events->sortBy('created_at') as $event)
        <x-event.list.item :event="$event"/>
    @empty
        <div>No Events</div>
    @endforelse 
</div>