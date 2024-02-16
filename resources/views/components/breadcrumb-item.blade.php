@props(['item', 'active' => null])
<x-fas-angle-double-right class="size-4" />
@isset($active)
    <strong>{{$item}}</strong>
@else
    {{$item}}
@endisset