<x-layout.base favicon_color="Orange">
    <x-slot:title>{{$title}}</x-slot>
    <x-slot:rightlogo>{{asset('/images/banners/documentation.png')}}</x-slot>
    <x-slot:menu>
      <x-menu.library />
    </x-slot>
    <x-slot:breadcrumbs>
        @isset($breadcrumbs)
            <x-breadcrumb-item item="Documentation" />
            {{$breadcrumbs}}
        @else   
            <x-breadcrumb-item class="active" item="Documentation" />
        @endisset
    </x-slot>      
    {{ $slot ?? '' }}
</x-layout.base>    
  