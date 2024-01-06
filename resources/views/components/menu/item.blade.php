@aware(['level'])
@props(['label', 'link' => null, 'dropdown' => null])
@isset($dropdown)
    @php 
        $mname = strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $label)) 
    @endphp
    <li x-data="{ {{$mname}} : false }" @@mouseover="{{$mname}} = true" @@mouseover.away="{{$mname}} = false" class="p-2 hover:bg-gray-300 relative">
        {{$label}}
        @if($level == 0)
            <x-fas-caret-down class="inline size-4" />
        @else
            <x-fas-caret-right class="inline size-4" />
        @endif
        <x-menu menuname="{{$mname}}" level="{{($level ?? 0) + 1}}">
            {{$slot}}
        </x-menu>
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