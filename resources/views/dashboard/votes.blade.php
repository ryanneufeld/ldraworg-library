<x-layout.main>
  <x-slot name="title">Votes for {{ Auth::user()->name }}</x-slot>
  @if (!empty($votes))
    <table class="ui striped celled sortable table">
      <thead>
        <tr>
          <th>Part</th>
          <th>Description</th>
          <th>Status</th>
          <th>My Vote</th>
        </tr>
      </thead>
      <tbody>
        @foreach($votes as $vote)
        <tr>
          <td>{{$vote->part->filename}}</td>
          <td><a href="{{ route('tracker.show',$vote->part->id) }}">{{$vote->part->description}}</a></td>
          <td><x-part.status :vote="unserialize($vote->part->vote_summary)" /></td>
          <td>{{ $vote->type->name ?? 0}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @else
    No votes found
  @endif
</x-layout.main>