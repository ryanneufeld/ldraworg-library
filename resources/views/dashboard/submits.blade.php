<x-layout.main>
  <x-slot name="title">Submits for {{ Auth::user()->name }}</x-slot>
  @isset($events)
    <table class="ui striped celled sortable table">
      <thead>
        <tr>
          <th>Part</th>
          <th>Description</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($events as $event)
        <tr>
          <td>{{$event->part->filename}}</td>
          <td><a href="{{ route('tracker.show',$event->part->id) }}">{{$event->part->description}}</a></td>
          <td><x-part.status :vote="unserialize($event->part->vote_summary)" /></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @else
    No submits found
  @endisset
</x-layout.main>