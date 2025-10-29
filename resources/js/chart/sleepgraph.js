import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

function createChart(id, labelText = '睡眠時間') {
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
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (context) {
              let value = context.parsed.y;
              return value.toFixed(1) + '時間';
            }
          }
        }
      },
      scales: {
        y: { title: { display: true }, beginAtZero: false },
        x: { title: { display: true, text: '' } }
      }
    }
  });
}

window.sleepTabs = function () {
  return {
    tab: 'week',
    charts: {},

    init() {
      // 初期描画で全タブを作っておく
      this.renderChart('week');
      this.renderChart('month');
      this.renderChart('year');
    },

    changeTab(name) {
      this.tab = name;
      this.$nextTick(() => {
        this.renderChart(name);
      });
    },

    renderChart(name) {
      const id = `sleep-${name}-chart`;
      if (!this.charts[name]) {
        this.charts[name] = createChart(id);
      }
    }
  }
}
