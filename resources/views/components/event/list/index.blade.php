@props(['events' => null, 'title' => ''])
<div class="ui medium header">{{$title}}</div>
<div class="ui large feed">
@forelse ($events->sortBy('created_at') as $event)
<x-event.list.item :event="$event" colspan="{{$event->part_event_type->slug == 'review' ? 2 : 1}}"/>
@if(!$loop->last)
<div class="ui divider"></div>
@endif
@empty
<div class="event"><div class="content">No Events</div></div>
@endforelse 
</div>