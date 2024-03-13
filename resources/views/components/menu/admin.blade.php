<x-menu>
    <x-menu.dropdown label="Library">
        <x-menu.item label="Library Main" link="{{route('index')}}" />
        <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
        <x-menu.item label="Latest Update" link="{{route('part-update.index', ['latest'])}}" />
        <x-menu.item label="Update Archive" link="{{route('part-update.index')}}" />
        <x-menu.item label="OMR" link="{{route('omr.main')}}" />
    </x-menu.dropdown>    
    <x-menu.item label="Add/Edit User" link="{{route('admin.users.index')}}" /> 
    <x-menu.item label="Add/Edit Roles" link="{{route('admin.roles.index')}}" />
    <x-menu.item label="Add/Edit Part Review Summaries" link="{{route('admin.summaries.index')}}" />
</x-menu>
