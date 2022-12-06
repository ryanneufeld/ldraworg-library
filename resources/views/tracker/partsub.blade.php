<x-layout.main>
  @foreach($parts as $part)
  <a href="{{route('tracker.show', $part->id)}}">{{$part->description}}</a><br/>
  @endforeach
</x-layout.main>
