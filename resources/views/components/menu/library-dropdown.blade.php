<x-menu.dropdown label="Library">
    <x-menu.item label="Library Main" link="{{route('index')}}" />
    <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
    <x-menu.item label="Latest Update" link="{{route('part-update.index', ['latest'])}}" />
    <x-menu.item label="Update Archive" link="{{route('part-update.index')}}" />
    <x-menu.item label="OMR" link="{{route('omr.main')}}" />
    <x-menu.dropdown label="Tools" level=1>
        <x-menu.item label="PBG Generator" link="{{route('pbg')}}" />
    </x-menu.dropdown>    
</x-menu.dropdown>