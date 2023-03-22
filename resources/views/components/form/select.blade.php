@props(['options', 'label', 'selected' => null, 'placeholder' => null, 'width' => null])
<div @class(["$width wide" => !is_null($width), 'field'])>
  <label for={{$attributes->get('name')}}>{{$label}}</label>
  <select {{$attributes->merge(['class' => 'ui dropdown'])}}>
    @isset($placeholder)
    <option value="">{{$placeholder}}</option>
    @endisset
    @foreach($options as $value => $text)
      <option value="{{$value}}" @selected($value == $selected)>{{$text}}</option>
    @endforeach
  </select>
</div>