import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('weight-week-chart')?.getContext('2d');
    if (!ctx) return;

    const weekLabels = JSON.parse(document.getElementById('weight-week-chart').dataset.labels);
    const weightData = JSON.parse(document.getElementById('weight-week-chart').dataset.data);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: weekLabels,
            datasets: [{
                label: '体重(kg)',
                data: weightData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { title: { display: true, }, beginAtZero: false },
                x: { title: { display: true, text: '日付' } }
            }
        }
    });
});
