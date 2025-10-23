<canvas id="chart" height="120"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  new Chart(document.getElementById('chart').getContext('2d'), {
    type: 'line',
    data: {
      labels: {!! json_encode($labels) !!},
      datasets: [{ label: 'Unique Visitors', data: {!! json_encode($values) !!}, tension: 0.25 }]
    },
    options: { responsive: true }
  });
</script>
