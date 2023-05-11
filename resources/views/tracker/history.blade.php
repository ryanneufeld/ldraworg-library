<x-layout.tracker>
    <x-slot:title>
        Parts Tracker History
    </x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="History" />
    </x-slot>    
    <h2 class="ui header">Parts Tracker History</h2>
    <div>
        <canvas id="historyChart" style="height: {{count($data) + 100}}px; width: 100%;"></canvas>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('historyChart');
            const data = {{ Js::from($data) }};

            new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(row => row.date),
                datasets: [
                    {
                        label: 'Held',
                        data: data.map(row => row.held),
                        backgroundColor: 'rgba(255, 0, 0, 1)',
                        barThickness: 1,
                    },
                    {
                        label: 'Uncertified Subfiles',
                        data: data.map(row => row.subparts),
                        backgroundColor: 'rgba(255, 255, 0, 1)',
                        barThickness: 1,
                    },
                    {
                        label: 'Needs Votes',
                        data: data.map(row => row.needsvotes),
                        backgroundColor: 'rgba(204, 204, 204, 1)',
                        barThickness: 1,
                    },
                    {
                        label: 'Needs Admin Review',
                        data: data.map(row => row.needsreview),
                        backgroundColor: 'rgba(0, 0, 255, 1)',
                        barThickness: 1,
                    },
                    {
                        label: 'Certified',
                        data: data.map(row => row.certified),
                        backgroundColor: 'rgba(0, 255, 0, 1)',
                        barThickness: 1,
                    },
                    
                ]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                animation: false,
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                    },
                }
            }
            });

        </script>
    @endpush
</x-layout.tracker>