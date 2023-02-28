<x-layout.main>
  <x-slot name="title">Recent Activity</x-slot>
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
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
        <th class="one wide">Event</th>
        <th>User</th>
        <th>Date/Time</th>
        <th>Image</th>
        <th>Part</th>
        <th>Description</th>
{{--        
        <th>Other Info</th>
--}}        
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($events as $event)
      @empty($event->part)
      {{dd($event)}}
      @endempty
      <tr>
        <td class="center aligned">
          <x-event.icon :event="$event"/>
          @if ($event->part_event_type->slug != 'rename' && $event->part_event_type->slug != 'comment' && !empty($event->comment))
          <i class="ui blue comment icon"></i>
          @endif
        </td>
        <td>{{$event->user->name ?? ''}}</td>
        <td>{{$event->created_at}}</td>
        <td>
          @if($event->part->isUnofficial())
          <img class="ui image" src="{{asset('images/library/unofficial/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
          @else
          <img class="ui image" src="{{asset('images/library/official/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
          @endif
        </td>      
        <td>{{$event->part->filename}}</td>
        <td><a href="{{ $event->part->isUnofficial() ? route('tracker.show',$event->part->id) : route('official.show',$event->part->id)}}">{{$event->part->description}}</a></td>
{{--        
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
--}}        
        <td><x-part.status :vote="$event->part->vote_summary" /></td>
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