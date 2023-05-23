import Chart from 'chart.js/auto';
let makeChart = function() {
    const ctx = document.getElementById('historyChart');

    new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(row => row.date),
        datasets: [
            {
                label: 'Held',
                data: chartData.map(row => row.held),
                backgroundColor: 'rgba(255, 0, 0, 1)',
                barThickness: 1,
            },
            {
                label: 'Uncertified Subfiles',
                data: chartData.map(row => row.subparts),
                backgroundColor: 'rgba(255, 255, 0, 1)',
                barThickness: 1,
            },
            {
                label: 'Needs Votes',
                data: chartData.map(row => row.needsvotes),
                backgroundColor: 'rgba(204, 204, 204, 1)',
                barThickness: 1,
            },
            {
                label: 'Needs Admin Review',
                data: chartData.map(row => row.needsreview),
                backgroundColor: 'rgba(0, 0, 255, 1)',
                barThickness: 1,
            },
            {
                label: 'Certified',
                data: chartData.map(row => row.certified),
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
}

document.addEventListener('DOMContentLoaded', makeChart, false);
