@props(['formId', 'buttonLabel' => 'Go'])
<div class="ui action input">
    {{$slot}}
    <button form="{{$formId}}" class="ui button">{{$buttonLabel}}</button>
</div>
