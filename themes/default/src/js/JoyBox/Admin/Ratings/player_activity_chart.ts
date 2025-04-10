interface ChartConfig {
  labels: string[];
  data: number[];
  options?: {
    title?: string;
    borderColor?: string;
    backgroundColor?: string;
    xAxisTitle?: string;
    yAxisTitle?: string;
  };
}

interface Window {
  activityChart?: Chart;
  activityChartConfig: ChartConfig;
}

document.addEventListener('DOMContentLoaded', (event: Event) => {
  const canvas = document.getElementById('activityChart') as HTMLCanvasElement;
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  if (!window.activityChartConfig) {
    console.error('Chart config is not defined');
    return;
  }

  const { labels, data, options } = window.activityChartConfig;

  // Установка значений по умолчанию
  const defaultOptions = {
    title: 'Активность игроков',
    borderColor: 'rgba(54, 162, 235, 1)',
    backgroundColor: 'rgba(54, 162, 235, 0.2)',
    xAxisTitle: 'Дата и время',
    yAxisTitle: 'Количество действий'
  };

  const mergedOptions = { ...defaultOptions, ...options };

  if (window.activityChart instanceof Chart) {
    window.activityChart.destroy();
  }

  window.activityChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: mergedOptions.title,
        data: data,
        backgroundColor: mergedOptions.backgroundColor,
        borderColor: mergedOptions.borderColor,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        x: {
          type: 'category',
          title: {
            display: true,
            text: mergedOptions.xAxisTitle
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: mergedOptions.yAxisTitle
          }
        }
      }
    }
  });
});
