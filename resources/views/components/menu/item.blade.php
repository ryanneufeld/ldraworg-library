@props(['label', 'link' => null, 'dropdown' => null])

@isset($dropdown)
{{--
    <div class="outline-none focus:outline-none border px-3 py-1 bg-white rounded-sm flex items-center min-w-32">
        <span class="pr-1 font-semibold flex-1">{{$label}}</span> 
        <i class="dropdown icon"></i>
        <x-menu submenu>
            {{$slot}}
        </x-menu>    
    </div>
--}}    
@else
    <li class="rounded-sm px-3 py-1 hover:bg-gray-100">
        @isset($link)
            <a class="item" href="{{$link}}">{{$label}}</a>
        @else
            {{$label}}
        @endisset        
    </li>
@endisset