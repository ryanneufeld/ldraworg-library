<x-layout.main>
<h3 class="ui header">{{basename($part->filename)}} Header Edit</h3>
@if ($errors->any())
  <div class="ui error message">
    <ul class="ui list">
    @foreach($errors->all() as $errorfield)
      <li>{{$errorfield}}</li>
    @endforeach
    </ul>    
  </div>
@endif
<form class="ui form" name="headeredit" action="{{route('tracker.doeditheader', $part->id)}}" method="POST">
@method('PUT')
@csrf
  <div class="field">
    <label>File</label>
      <textarea name="h" rows="{{$rows ?? 10}}">{{old('h') ?? $part->header}}</textarea>
  </div>
  <button class="ui button" type="submit" tabindex="20">Submit</button>
</form>
</x-layout.main>