<x-layout.tracker>
  <x-slot:title>Create Release Step 2</x-slot>
  <form class="ui form" action="{{route('tracker.release.store')}}" method="post">
    @csrf
    @foreach($parts as $part)
      <input name="ids[]" value="{{$part->id}}" type="hidden">
    @endforeach
    <x-part.table title="File to be release" :parts="$parts" />
    <h4 class="ui header">Files for ldraw folder:</h4>
    @foreach($files as $file)
    <a href="{{$file}}">{{basename($file)}}</a><br>
    @endforeach
    <div class="inline field">
      <div class="ui checkbox">
        <input type="checkbox" name="approve">
        <label>Initate Release</label>
      </div>
    </div>  
    <button class="ui button" type="submit">Submit</button>
  </form>    
</x-layout.tracker>