@props(['header'])

<div x-data="{ {{$attributes->get('id')}} : false }" class="flex flex-col">
    <div {{$header->attributes->class(['flex flex-row'])}} >

        <x-fas-caret-right class="size-4 place-self-center" ::class="{{$attributes->get('id')}} && 'rotate-90'" @@click="{{$attributes->get('id')}} = !{{$attributes->get('id')}}" />
        {{$header}}
    </div>
    <div 
        x-show="{{$attributes->get('id')}}" 
        x-transition:enter="transition ease-out duration-100" 
        x-transition:enter-start="transform opacity-0"
        x-cloak
    >
        {{$slot}}
    </div>
</div>