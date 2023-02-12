@props(['event'])
<i title="{{$event->part_event_type->name}}" @class([
  'red' => $event->vote_type_code == 'H', 
  'green' => $event->vote_type_code == 'C',
  'olive' => $event->vote_type_code == 'T' || $event->vote_type_code == 'A',
  'blue comment' => $event->part_event_type->slug == 'comment',
  'file' => $event->part_event_type->slug == 'submit',
  'edit' => $event->part_event_type->slug == 'edit',
  'file export' => $event->part_event_type->slug == 'rename',
  'exclamation circle' => $event->part_event_type->slug == 'review' && $event->vote_type_code == 'H',
  'undo' => $event->part_event_type->slug == 'review' && is_null($event->vote_type_code),
  'check' => $event->part_event_type->slug == 'review' && !is_null($event->vote_type_code) && $event->vote_type_code != 'H',
  'icon',
])></i>
