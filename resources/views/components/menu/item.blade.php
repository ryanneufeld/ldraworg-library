@props(['label', 'link' => null, 'hidden' => null])
<li {{$attributes->merge(['class'=>'p-2 hover:bg-gray-300'])}}>
    @isset($link)
        <a {{$attributes->merge(['class' => 'item'])}} href="{{$link}}">{{$label}}</a>
    @else
        {{$label}}
    @endisset
</li>
