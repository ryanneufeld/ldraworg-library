@props(['value' => null, 'label' => '', 'formats' => \App\Models\PartType::pluck('format')->unique()->values()->all()])
<div class="grouped fields">
  @foreach($formats as $format)
    <label for="part_type_id">{{$label}} (.{{$format}} files)</label>
    @foreach (\App\Models\PartType::where('type', '<>', 'Shortcut')->where('format', $format)->get() as $part_type)
     <div class="field">
        <div class="ui radio checkbox">
          <input type="radio" name="part_type_id" value="{{$part_type->id}}" @checked($part_type->id == $value || (is_null($value) && $loop->index == 0 && $loop->parent->index == 0))>
          <label>{{$part_type->folder}}
          @if ($part_type->name == 'Part')
          ({{$part_type->name . ", Shortcut"}})
          @else
          ({{$part_type->name}})
          @endif
          </label>
        </div>
      </div>
    @endforeach
  @endforeach
</div>