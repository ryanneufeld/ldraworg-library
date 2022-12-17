<x-layout.main>
  <x-slot name="title">Recent Activity</x-slot>
    <div class="ui right floated right aligned basic segment">
    Server Time: {{date('r')}}<br/>
    <x-part.unofficial-count :summary="$summary"/>
  </div>

  <h3 class="ui header">Parts Tracker Activity Log</h3>
  <div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{$events->previousPageUrl()}}">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" HREF="{{$events->nextPageUrl()}}">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{route('tracker.activity')}}">Newest</a>
    @endif
  </div>  
  <table class="ui striped celled table">
    <thead>
      <tr>
        <th>Date/Time</th>
        <th>Event</th>
        <th>Part</th>
        <th>Description</th>
        <th>User</th>
        <th>Other Info</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($events as $event)
      <tr>
        <td>{{$event->created_at}}</td>
        <td>{{$event->part_event_type->name}}</td>
        <td>{{$event->part->filename}}</td>
        <td><a href="{{ $event->part->unofficial ? route('tracker.show',$event->part->id) : route('official.show',$event->part->id)}}">{{$event->part->description}}</a></td>
        <td>{{$event->user->name ?? ''}}</td>
        <td>
          @if ($event->part_event_type->slug == 'review' || $event->part_event_type->slug == 'submit')
            @if ($event->part_event_type->slug == 'review')
              @isset ($event->vote_type_code)
                {{ $event->vote_type->name }}
              @else
                Vote cancel 
              @endisset 
            @endif  
            @empty($event->comment)
              with no comments
            @else
              with comments
            @endempty 
          @endif
        </td>
        <td><x-part.status :vote="unserialize($event->part->vote_summary)" /></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{$events->previousPageUrl()}}">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" HREF="{{$events->nextPageUrl()}}">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{route('tracker.activity')}}">Newest</a>
    @endif
  </div>  
</x-layout.main>