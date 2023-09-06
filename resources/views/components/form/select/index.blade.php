@props([
  'options', 
  'label' => null, 
  'selected' => null, 
  'placeholder' => null, 
])

@if(!empty($label))  
    <label for={{$attributes->get('name')}}>{{$label}}</label>
@endif
<div {{$attributes->class(['ui selection dropdown'])}}>
    <input {{$attributes->whereStartsWith('wire:')}} type="hidden" {{$attributes->get('name')}} value="{{$selected}}">
    <i class="dropdown icon"></i>
    <div class="default text">{{$placeholder}}</div>
    <div class="scrollhint menu">
        @foreach($options as $value => $text)
            <div class="item" data-value="{{$value}}">{{$text}}</div>
        @endforeach
    </div>    
</div>
