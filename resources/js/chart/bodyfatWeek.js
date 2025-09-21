import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

function createChart(id, labelText = '体脂肪率(%)') {
    const el = document.getElementById(id);
    if (!el) return;

    const labels = JSON.parse(el.dataset.labels);
    const data = JSON.parse(el.dataset.data);

    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: labelText,
                data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.3,
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { title: { display: true }, beginAtZero: false },
                x: { title: { display: true, text: '' } }
            }
        }
    });
}

window.bodtfatTabs = function() {
    return {
        tab: 'week',
        charts: {},

        init() {
            // 初期タブ描画
            this.charts.week = createChart('bodtfat-week-chart');
        },

        changeTab(name) {
            this.tab = name;
            this.$nextTick(() => {
                if (!this.charts[name]) {
                    this.charts[name] = createChart(`bodtfat-${name}-chart`);
                }
            });
        }
    }
}
