<div class="ui medium header">{{$title}}</div>
@forelse ($events->sortBy('created_at') as $event)
<div class="ui segment">
At {{ $event->created_at }},
  @switch($event->part_event_type->slug)
    @case('submit')
      the file was submitted<br>
      User: {{$event->user->name}}
      @isset($event->comment)
      <br>Comments:<br>
      <strong>{!! nl2br($event->comment) !!}</strong>
      @endisset
      @break
    @case('review')
      the following review was posted:<br>
      User: {{$event->user->name ?? 'No user'}}<br>
      Certification: {{$event->vote_type->name ?? 'Vote Canceled'}}
      @isset($event->comment)
      <br>Comments:<br>
      <strong>{!! nl2br($event->comment) !!}</strong>
      @endisset
      @break
    @case('comment')
      the following non-voting comment was posted:<br>
      User: {{$event->user->name}}<br>
      Comment:<br>
      <strong>{!! nl2br($event->comment) !!}</strong>
      @break
    @case('rename')
      {!! nl2br($event->comment) !!}
      @break
    @case('edit')
      the header was edited by a Parts Tracker admin.
      @break
  @endswitch    
</div>
@empty
<div class="ui segment">No Events</div>
@endforelse 
