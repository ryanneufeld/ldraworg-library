<x-layout.base>
    <x-slot:title>{{$title}}</x-slot>
    <x-slot:rightlogo>{{asset('/images/banners/tracker.png')}}</x-slot>
    <x-slot:menu>
      <x-menu.tracker />
    </x-slot>
    <x-slot:breadcrumbs>
        @isset($breadcrumbs)
            <x-breadcrumb-item item="Parts Tracker" />
            {{$breadcrumbs}}
        @else   
            <x-breadcrumb-item class="active" item="Parts Tracker" />
        @endisset
    </x-slot>      
    {{ $slot ?? '' }}
</x-layout.base>    
  