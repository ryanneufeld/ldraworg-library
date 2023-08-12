@props(['event', 'colspan' => 1])
  <div class="event">
    <div class="label">
      <x-event.icon :event="$event" />
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
            @if(isset($event->initial_submit) && $event->initial_submit == true)
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
              cancelled their vote.
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
      <div class="partfeed extra text">
        @if($event->part_event_type->slug == 'edit' && !is_null($event->header_changes))
          <x-event.list.edit-accordian :changes="$event->header_changes" />
          @if(!is_null($event->comment))  
            Comment:<br>
          @endif    
        @endif            
        {!! $event->processedComment() !!}
      </div>
    </div>
  </div>