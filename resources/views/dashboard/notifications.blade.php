<x-layout.main>
  <x-slot name="title">Notifications list for {{ Auth::user()->name }}</x-slot>
  @if (Auth::user()->has('part_events'))
    <table class="ui striped celled sortable table">
      <thead>
        <tr>
          <th>Part</th>
          <th>Description</th>
          <th>Status</th>
          <th>Last Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach(Auth::user()->part_events()->with(['part','part_event_type','vote_type'])->get()->sortByDesc('part_event.created_at')->unique('part.filename')->sortBy('part.description')->values()->all() as $event)
        <tr>
          <td>{{$event->part->filename}}</td>
          <td><a href="{{ route('library.tracker.show',$event->part->id) }}">{{$event->part->description}}</a></td>
          <td><x-part.status :part="$event->part" /></td>
          <td>
            @if($event->part_event_type->slug == 'review')
            {{$event->vote_type->name}}
            @else
            {{$event->part_event_type->name}}
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @else
    No submits found
  @endif
</x-layout.main>