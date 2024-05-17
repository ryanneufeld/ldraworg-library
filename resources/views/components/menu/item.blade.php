@props(['label', 'link' => null, 'hidden' => null])
@isset($link)
    <a {{$attributes->merge(['class' => 'item'])}} href="{{$link}}">
@endisset
    <li {{$attributes->merge(['class'=>'p-2 hover:bg-gray-300'])}}>{{$label}}</li>
@isset($link)
    </a>
@endisset
