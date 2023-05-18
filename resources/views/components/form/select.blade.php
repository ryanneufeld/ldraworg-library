@props([
  'options', 
  'label' => null, 
  'selected' => null, 
  'placeholder' => null, 
  'width' => null, 
  'defer' => false, 
  'actionInput' => false, 
  'formId' => '', 
  'buttonLabel' => 'Go'
])
<div @class(["$width wide" => !is_null($width), 'field'])>
  @if(!empty($label))  
    <label for={{$attributes->get('name')}}>{{$label}}</label>
  @endif
  @if($actionInput)
  <div class="ui action input">
  @endif  
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
  @if($actionInput)
  <button form="{{$formId}}" class="ui button">{{$buttonLabel}}</button>
  </div>
  @endif  
</div>
