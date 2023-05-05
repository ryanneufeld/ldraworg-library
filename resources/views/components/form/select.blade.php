@props(['options', 'label', 'selected' => null, 'placeholder' => null, 'width' => null, 'defer' => false])
@empty($attributes->get('wire:ignore'))
<div @class(["$width wide" => !is_null($width), 'field'])>
@else
<div wire:ignore @class(["$width wide" => !is_null($width), 'field'])>
@endempty  
  <label for={{$attributes->get('name')}}>{{$label}}</label>
  <select {{$attributes->merge(['class' => 'ui dropdown'])}}>
    @isset($placeholder)
      <option value="">{{$placeholder}}</option>
    @endisset
    @foreach($options as $value => $text)
      @empty($attributes->get('multiple'))
        <option value="{{$value}}" @selected($value == $selected)>{{$text}}</option>
      @else
        <option value="{{$value}}" @selected(in_array($value, $selected ?? []))>{{$text}}</option>
      @endempty
    @endforeach
  </select>
  @if (!empty($attributes->get('wire:ignore')))
  @push('scripts')
  <script>
  $( function() {
    $(@js('#' . $attributes->get('id'))).on('change', function (e) {
      @empty($attributes->get('multiple'))
        var data = $(@js('#' . $attributes->get('id') . ' option:selected')).val();
      @else
        var data = $(@js('#' . $attributes->get('id'))).val(); 
      @endempty
      @this.set(@js($attributes->get('id')), data, @js($defer));   
    });
  });
  </script>
  @endpush
  @endif
</div>
