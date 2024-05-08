<x-slot name="title">{{$title}}</x-slot>
<x-slot:menu>
    <x-menu.admin />
</x-slot>
<x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="{{$title}}" />
</x-slot>    
<div>
    {{ $this->table }}
</div>
