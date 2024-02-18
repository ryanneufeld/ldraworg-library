<x-menu>
    <x-menu.item label="Library Main" link="{{route('index')}}" /> 
    <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
    <x-menu.item label="Update" link="{{route('part-update.index')}}" />
    <x-menu.item label="Documentation" link="https://www.ldraw.org/docs-main.html" />
    <x-menu.item label="OMR" link="{{route('omr.main')}}" />
</x-menu>
