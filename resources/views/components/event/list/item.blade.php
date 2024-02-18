@props(['event'])
<div class="flex-flex-col rounded border p-4">
    <div class="flex flex-row space-x-4 place-items-center ">
        <x-event.icon :event="$event" />
        <div class="font-bold">
            @if($event->user->role != 'Synthetic User'  && $event->user->role != 'Legacy User' && $event->user->name != 'PTadmin')
            <a class="text-blue-500 visited:text-violet-500 hover:text-blue-300 hover:underline" href="https://forums.ldraw.org/private.php?action=send&uid={{$event->user->forum_user_id}}&subject=Regarding%20Parts%20Tracker%20file%20{{$event->part->filename}}">
              {{ $event->user->author_string }}
            </a>  
            @else
              {{ $event->user->author_string }}
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
        </div>
        <div class="text-xs text-gray-500">
          {{ $event->created_at }}
        </div>
    </div>
    <p class="mt-4">
        @if($event->part_event_type->slug == 'rename')
            "{{$event->moved_from_filename}}" to "{{$event->moved_to_filename}}"
        @endif            
        @if($event->part_event_type->slug == 'edit' && !is_null($event->header_changes))
          <x-event.list.edit-accordian :changes="$event->header_changes" />
          @if(!is_null($event->comment))  
            Comment:<br>
          @endif    
        @endif
        @if(!is_null($event->comment) && $event->part_event_type->slug !== 'rename')            
            {!! $event->processedComment() !!}
        @endif
    </p>
</div>