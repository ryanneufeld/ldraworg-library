<x-layout.main>
  <h4 class="ui header'">The following files passed validation checks and have been submitted to the Parts Tracker</h4>
  <ul class="ui list">
    @foreach($parts as $part)
    <li></li>{{$part->nameString()}} - <a href="{{route('tracker.show', $part->id)}}">{{$part->description}}</a></li>
    @endforeach
  </ul>
</x-layout.main>
