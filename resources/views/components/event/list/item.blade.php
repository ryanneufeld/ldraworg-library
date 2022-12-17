@props(['event', 'colspan' => 1])
  <div class="event">
    <div class="label">
      <i @class([
        'red' => $event->vote_type_code == 'H', 
        'green' => $event->vote_type_code == 'C',
        'olive' => $event->vote_type_code == 'T' || $event->vote_type_code == 'A',
        'file' => $event->part_event_type->slug == 'submit',
        'edit' => $event->part_event_type->slug == 'edit' || $event->part_event_type->slug == 'rename',
        'exclamation circle' => $event->part_event_type->slug == 'review' && $event->vote_type_code == 'H',
        'check double' => $event->part_event_type->slug == 'review',
        'comment' => $event->part_event_type->slug == 'comment',
        'icon',
      ])></i>
    </div>
    <div class="content">
      <div class="partevent summary">
        @if($event->user->role != 'Synthetic User'  && $event->user->role != 'Legacy User' && $event->user->name != 'PTadmin')
        <a class="user" href="https://forums.ldraw.org/private.php?action=send&uid={{$event->user->forum_user_id}}&subject=Regarding%20Parts%20Tracker%20file%20{{$event->part->filename}}">
          {{ $event->user->authorString() }}
        </a>  
        @else
          {{ $event->user->authorString() }}
        @endif
        @switch($event->part_event_type->slug)
          @case('submit')
            @isset($event->initial_submit)
              initially submitted the part.
            @else
              submitted a new version of the part.
            @endisset
          @break
          @case('edit')
          edited the part header.
          @break
          @case('review')
            @empty($event->vote_type_code)
              cancelled thier vote.
            @else
              posted a vote of {{$event->vote_type->name}}.
            @endempty
          @break
          @case('comment')
          made the following comment.
          @break
          @case('rename')
          renamed the part.
          @break
        @endswitch    
        <div class="date">
          {{ $event->created_at }}
        </div>
      </div>
      <div class="extra text">
        {!! nl2br(htmlspecialchars(html_entity_decode($event->comment))) !!}
      </div>
    </div>
  </div>