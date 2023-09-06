@props([
    'label' => '',
])
@if(!empty($label))  
<label for={{$attributes->get('name')}}>{{$label}}</label>
@endif
<div class="ui calendar">
    <div class="ui fluid input left icon">
        <i class="calendar icon"></i>
        <input type="text" {{$attributes->get('placeholder')}} {{$attributes->whereStartsWith('wire:')}} {{$attributes->get('name')}} {{$attributes->get('value')}}>
    </div>
</div>
