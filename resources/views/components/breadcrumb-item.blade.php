@props(['item', 'active' => null])
<div class="px-2">
    <x-fas-angle-double-right class="size-4" />
</div>
<div @class(["font-bold" => !is_null($active)])>
    {{$item}}
</div>