@foreach($events as $event)
@if (!$loop->first)
==
@endif
@switch($event->part_event_type->slug)
@case('submit')
A new version of file {{$event->part->filename}} '{{$event->part->description}}' was submitted by {{$event->user->name}}
@break
@case('edit') 
The header of part {{$event->part->filename}} '{{$event->part->description}}' was edited by {{$event->user->name}}
@break
@case('rename') 
{{$event->comment}} by {{$event->user->name}}
@break
@case('comment') 
{{$event->user->name}} made a comment about {{$event->part->filename}} '{{$event->part->description}}'
@break
@case('release') 
{{$event->part->filename}} '{{$event->part->description}}' was released in update {{$event->release->name}}
@break
@case('review')
@empty($event->vote_type_code) 
{{$event->user->name}} cancelled thier vote on {{$event->part->filename}} '{{$event->part->description}}'
@else
{{$event->user->name}} left a {{strtolower($event->vote_type->name)}} vote on {{$event->part->filename}} '{{$event->part->description}}'
@endempty
@break
@endswitch
See {{route('tracker.show', $event->part)}}
@endforeach