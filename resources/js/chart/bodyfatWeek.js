import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

function createChart(id, labelText = '体脂肪率(%)') {
    const el = document.getElementById(id);
    if (!el) return null;

    // 既に描画済みなら破棄
    const existingChart = Chart.getChart(el);
    if (existingChart) existingChart.destroy();

    const labels = JSON.parse(el.dataset.labels || '[]');
    const data = JSON.parse(el.dataset.data || '[]');

    return new Chart(el.getContext('2d'), {
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
            scales: { y: { beginAtZero: false }, x: {} }
        }
    });
}

window.bodtfatTabs = function() {
    return {
        tab: 'week',
        charts: {},

        init() {
            // 初期タブ描画
            this.renderChart('week');
            this.renderChart('month'); // 非表示タブも描画
            this.renderChart('year');  // 非表示タブも描画
        },

        changeTab(name) {
            this.tab = name;
            this.$nextTick(() => {
                this.renderChart(name);
            });
        },

        renderChart(name) {
            const id = `bodtfat-${name}-chart`;
            if (!this.charts[name]) {
                this.charts[name] = createChart(id);
            }
        }
    }
}
