<x-menu>
    <x-menu.dropdown label="Library">
        <x-menu.item label="Library Main" link="{{route('index')}}" />
        <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
        <x-menu.item label="Latest Update" link="{{route('part-update.index', ['latest'])}}" />
        <x-menu.item label="Update Archive" link="{{route('part-update.index')}}" />
        <x-menu.item label="OMR" link="{{route('omr.main')}}" />
    </x-menu.dropdown>
    @can('create', App\Models\Omr\OmrModel::class)
        <x-menu.item label="Submit" link="" />
    @endcan
    <x-menu.item label="Model List" link="{{route('omr.sets.index')}}" />
    <x-menu.item label="Statistics" link="" />
    <x-menu.dropdown label="Documentation">
        <x-menu.item label="Official Model Repository (OMR) Specification" link="https://www.ldraw.org/article/593.html" />
        <x-menu.item label="Rules and procedures for the Official Model Repository" link="https://www.ldraw.org/docs-main/official-model-repository-omr/rules-and-procedures-for-the-official-model-repository.html" />
    </x-menu.dropdown>
</x-menu>