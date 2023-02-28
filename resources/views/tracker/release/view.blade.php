<x-layout.main>
  <h3 class="ui header">{{$release->name}} New Parts Preview</h3>
  @isset($release->part_list) 
    @foreach($release->part_list as list($description, $filename))
      <h5 class="ui header">{{$filename}} - {{$description}}</h5>
      <img class= "ui image" src="{{asset('images/library/updates/view' . $release->short . '/' . substr($filename, 0, -4) . '.png')}}">
    @endforeach  
  @else
    No parts in this release or preview has not been generated for this release
  @endisset
</x-layout.main>