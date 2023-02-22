<x-layout.main>
  <form class="ui form" action="{{route('tracker.release.create', ['step' => 3])}}" method="post">
    @csrf
    @foreach($ids as $id)
      <input type="hidden" name="ids[]" value="{{$id}}">
    @endforeach
    <h5 class="ui header">Release Files:</h5>
    <div class="ui segment">
      <a href="{{asset($zips['update'])}}">Release zip</a><br>
      <a href="{{asset($zips['complete'])}}">Complete zip</a>
    </div>
    <h5 class="ui header">Notes File:</h5>
    <div class="ui scrolling segment">
      <pre>{{$notes}}</pre>
    </div>
    <div class="inline field">
      <div class="ui checkbox">
        <input type="checkbox" name="approve" class="hidden">
        <label>Initate Release</label>
      </div>
    </div>  
    <button class="ui button" type="submit">Submit</button>
  </form>    
</x-layout.main>