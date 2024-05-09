<x-menu>
    <x-menu.dropdown label="Library">
        <x-menu.item label="Library Main" link="{{route('index')}}" />
        <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
        <x-menu.item label="Latest Update" link="{{route('part-update.index', ['latest'])}}" />
        <x-menu.item label="Update Archive" link="{{route('part-update.index')}}" />
        <x-menu.item label="OMR" link="{{route('omr.main')}}" />
    </x-menu.dropdown>
    @can('create', \App\Models\User::class)    
        <x-menu.item label="Add/Edit User" link="{{route('admin.users.index')}}" />
    @endcan 
    @can('viewAny', \Spatie\Permission\Models\Role::class)    
        <x-menu.item label="Add/Edit Roles" link="{{route('admin.roles.index')}}" />
    @endcan 
    @can('viewAny', \App\Models\ReviewSummary\ReviewSummary::class)    
        <x-menu.item label="Add/Edit Part Review Summaries" link="{{route('admin.summaries.index')}}" />
    @endcan 
    @can('library.settings.edit')    
        <x-menu.item label="Add/Edit Default Part Render" link="{{route('admin.part-render-views.index')}}" />
    @endcan 
</x-menu>
