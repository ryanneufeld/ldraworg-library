<x-layout.tracker>
    <x-slot:title>
        Parts Tracker History
    </x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="History" />
    </x-slot>    
    <h2 class="ui header">Parts Tracker History</h2>
    <div>
        {!! $chart->render() !!}
    </div>
</x-layout.tracker>