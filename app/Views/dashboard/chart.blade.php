<div class="text-center p-8">
    <h3 class="text-lg font-bold mb-4">Chart Placeholder</h3>
    <div class="text-sm text-gray-500">Chart data: {{ count($labels ?? []) }} days</div>
    <div class="mt-4">
        @if(isset($labels) && count($labels) > 0)
            <canvas id="simpleChart" width="400" height="200"></canvas>
            <script>
                const ctx = document.getElementById('simpleChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($labels) !!},
                        datasets: [{
                            label: 'Unique Visitors',
                            data: {!! json_encode($values) !!},
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Daily Unique Visitors'
                            }
                        }
                    }
                });
            </script>
        @else
            <div class="text-gray-500">No chart data available</div>
        @endif
    </div>
</div>
