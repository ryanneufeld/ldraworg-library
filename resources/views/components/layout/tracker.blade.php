<x-layout.base title="{{$title}}">
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
  