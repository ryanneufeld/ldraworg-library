@props(['event', 'type' => 'feed'])
@if(($event->part_event_type->slug == 'review' && is_null($event->vote_type_code)) || ($event->part_event_type->slug != 'comment' && !is_null($event->comment) && $type == 'table'))
<i class="icons">
@endif
<i title="{{$event->part_event_type->name}}" @class([
  'red' => $event->vote_type_code == 'H', 
  'green' => $event->vote_type_code == 'C',
  'olive' => $event->vote_type_code == 'T' || $event->vote_type_code == 'A',
  'blue comment' => $event->part_event_type->slug == 'comment',
  'file' => $event->part_event_type->slug == 'submit',
  'edit' => $event->part_event_type->slug == 'edit',
  'file export' => $event->part_event_type->slug == 'rename',
  'graduation cap' => $event->part_event_type->slug == 'release',
  'exclamation circle' => $event->part_event_type->slug == 'review' && $event->vote_type_code == 'H',
  'vote yea icon' => $event->part_event_type->slug == 'review' && is_null($event->vote_type_code),
  'check' => $event->part_event_type->slug == 'review' && !is_null($event->vote_type_code) && $event->vote_type_code != 'H',
  'big' => $type == 'table',
  'icon',
])></i>
@if(($event->part_event_type->slug == 'review' && is_null($event->vote_type_code)) || (!is_null($event->comment) && $type == 'table'))
@if($event->part_event_type->slug == 'review' && is_null($event->vote_type_code))
<i @class(['big' => $type == 'table', 'red slash icon'])></i>
@endif
@if($event->part_event_type->slug != 'comment' && !is_null($event->comment) && $type == 'table')
<i @class(['small' => $type == 'table', 'blue top right corner comment icon'])></i>
@endif
</i>
@endif
  

