@props([
    'label',
    'type' => '',
])
<div class="ui {{$type}} checkbox">
    <input type="checkbox" {{$attributes->whereStartsWith('wire:')}} name="{{$attributes->get('name')}}" tabindex="0" class="hidden">
    <label>{{$label}}</label>
</div>
