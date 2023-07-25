<x-layout.tracker>
  <x-slot:title>Move Part</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Move Part" />
  </x-slot>    
  @if ($errors->any())
    <div class="ui error message">
      <ul class="ui list">
      @foreach($errors->all() as $errorfield)
        <li>{{$errorfield}}</li>
      @endforeach
      </ul>    
    </div>
  @endif
  @if(!$part->isUnofficial() && !is_null($part->unofficial_part_id))
    An update to this part is already on the Parts Tracker. Please use the normal submit process
  @else  
  <form class="ui form" action="{{route('tracker.move.update', $part)}}" method="post">
    @csrf
    @method('PUT')
    <div class="ui field">
      <label>Current Location:</label>
      <input class="transparent" type="text" value="{{$part->type->folder}} ({{$part->type->name}})" readonly >
    </div>
    <div class="ui field">
      <label>Current Name:</label>
      <input class="transparent" type="text" name="oldname" value="{{basename($part->filename)}}" readonly >
    </div>
    <x-form.radio.part-type-move value="{{$part->type->folder}}" format="{{$part->type->format}}"/>
    {{--<x-type.radio value="{{$part->part_type_id}}" label="Move destination" :formats="[$part->type->format]"/>--}}
    <div class="ui field">
      <label>New Name (Note: exclude the folder from the name)</label>
      <input type="text" name="newname" placeholder="New Name">
    </div>
    <div class="field">
      <button class="ui button" type="submit">Submit</button>
    </div>
  </form>
  @endif
</x-layout.tracker>