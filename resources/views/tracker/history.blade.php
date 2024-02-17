<x-layout.tracker>
    <x-slot:title>
        Parts Tracker History
    </x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="History" />
    </x-slot>    
    <div class="text-2xl font-bold">Parts Tracker History</div>
    <div>
        {!! $chart->render() !!}
    </div>
</x-layout.tracker>