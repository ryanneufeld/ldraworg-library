@props(['label', 'link' => null, 'dropdown' => null])

@isset($dropdown)
    <div class="ui dropdown item">
        {{$label}} 
        <i class="dropdown icon"></i>
        <x-menu submenu>
            {{$slot}}
        </x-menu>    
    </div>
@else
    @isset($link)
        <a class="item" href="{{$link}}">{{$label}}</a>
    @else
        <div class="item">{{$label}}</div>
    @endisset
@endisset