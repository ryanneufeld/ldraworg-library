<x-layout.tracker>
    <x-slot:title>
        Parts Tracker History
    </x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="History" />
    </x-slot>    
    <h2 class="ui header">Parts Tracker History</h2>
    <div>
        <canvas id="historyChart" style="height: 5010px; width: 100%;"></canvas>
    </div>
    @push('scripts')
        <script src="{{ mix('assets/js/history.js') }}" type="text/javascript"></script>
        <script>
            const chartData = {{ Js::from($data) }}            
        </script>
    @endpush
</x-layout.tracker>