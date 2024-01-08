@props(['label', 'link' => null])
<li class="p-2 hover:bg-gray-300">
    @isset($link)
        <a class="item" href="{{$link}}">{{$label}}</a>
    @else
        {{$label}}
    @endisset
</li>
