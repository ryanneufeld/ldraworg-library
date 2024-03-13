<x-layout.base>
    <x-slot:title>{{$title}}</x-slot>
    <x-slot:menu>
      <x-menu.admin />
    </x-slot>
    <x-slot:breadcrumbs>
        @isset($breadcrumbs)
            <x-breadcrumb-item item="Admin" />
            {{$breadcrumbs}}
        @else   
            <x-breadcrumb-item class="active" item="Admin" />
        @endisset
    </x-slot>      
    {{ $slot ?? '' }}
</x-layout.base>    
  