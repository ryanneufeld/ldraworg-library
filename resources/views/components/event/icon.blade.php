@props(['event', 'type' => 'feed'])
@if((!is_null($event->comment) && !in_array($event->part_event_type->slug, ['comment', 'release', 'delete', 'rename']) && $type == 'table') || 
($event->initial_submit === true && $event->part_event_type->slug === 'submit' && !is_null($event->part->official_part_id) && $type == 'table'))
<i class="icons">
@endif
<i title=" @if($event->part_event_type->slug == 'review') {{$event->part_event_type->name}}: {{$event->vote_type->name ?? 'Vote Cancel'}} @else {{$event->part_event_type->name}} @endif "
  @class([
  'red' => $event->vote_type_code == 'H', 
  'green' => $event->vote_type_code == 'C',
  'olive' => $event->vote_type_code == 'T' || $event->vote_type_code == 'A',
  'blue comment' => $event->part_event_type->slug == 'comment',
  'recycle' => $event->part_event_type->slug == 'delete',
  'file' => $event->part_event_type->slug == 'submit',
  'edit' => $event->part_event_type->slug == 'edit',
  'file export' => $event->part_event_type->slug == 'rename',
  'graduation cap' => $event->part_event_type->slug == 'release',
  'exclamation circle' => $event->part_event_type->slug == 'review' && $event->vote_type_code == 'H',
  'undo' => $event->part_event_type->slug == 'review' && is_null($event->vote_type_code),
  'check' => $event->part_event_type->slug == 'review' && !is_null($event->vote_type_code) && $event->vote_type_code != 'H',
  'big' => $type == 'table',
  'icon',
])></i>
@if(!is_null($event->comment) && !in_array($event->part_event_type->slug, ['comment', 'release', 'delete', 'rename']) && $type == 'table')
<i @class(['small' => $type == 'table', 'blue bottom left corner comment icon'])></i>
</i>
@elseif($event->initial_submit === true && $event->part_event_type->slug === 'submit' && !is_null($event->part->official_part_id) && $type == 'table')
<i @class(['small' => $type == 'table', 'green bottom left corner tools icon'])></i>
</i>
@endif
  

