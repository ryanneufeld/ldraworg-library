@props(['label', 'link' => null, 'dropdown' => null, 'toplevel' => null])
@isset($dropdown)
    @php 
        $mname = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $label)) 
    @endphp
    <li x-data="{ {{$mname}} : false }" @@mouseover="{{$mname}} = true" @@mouseover.away="{{$mname}} = false" class="p-2 hover:bg-gray-300 relative">
        {{$label}}
        @isset($toplevel)
            <x-fas-caret-down class="inline size-4" />
            <x-menu menuname="{{$mname}}" submenu >
                {{$slot}}
            </x-menu>
        @else
            <x-fas-caret-right class="inline size-4" />
            <x-menu menuname="{{$mname}}" submenu nested>
                {{$slot}}
            </x-menu>
        @endisset    
    </li>
@else
    <li class="p-2 hover:bg-gray-300">
        @isset($link)
            <a class="item" href="{{$link}}">{{$label}}</a>
        @else
            {{$label}}
        @endisset
    </li>
@endisset