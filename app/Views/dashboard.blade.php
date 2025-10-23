@extends('layouts.base')

@section('content')
<div class="space-y-6">
    <!-- Header with period selector -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Traffic Dashboard</h1>
        <div class="flex gap-2">
            <select id="periodSelector" class="select select-bordered" onchange="updatePeriod()">
                <option value="7" {{ ($selected_period ?? 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($selected_period ?? 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ ($selected_period ?? 30) == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow">
            <div class="stat-title">Total Visits</div>
            <div class="stat-value text-info">{{ number_format($total_visits ?? 0) }}</div>
            <div class="stat-desc">All page views (Last {{ $period ?? 30 }} days)</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow">
            <div class="stat-title">Unique Visitors</div>
            <div class="stat-value text-primary">{{ number_format($total_visitors ?? 0) }}</div>
            <div class="stat-desc">Distinct users (Last {{ $period ?? 30 }} days)</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow">
            <div class="stat-title">Most Popular Page</div>
            <div class="stat-value text-sm">
                @if(isset($top_pages) && count($top_pages) > 0)
                    {{ $top_pages[0]['page_url'] ?? 'No data yet' }}
                @else
                    No data yet
                @endif
            </div>
            <div class="stat-desc">
                @if(isset($top_pages) && count($top_pages) > 0)
                    {{ $top_pages[0]['total_visits'] ?? 0 }} total visits
                @else
                    Top page
                @endif
            </div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow">
            <div class="stat-title">Tracking Status</div>
            <div class="stat-value text-success text-sm">
                @if(($total_visitors ?? 0) > 0)
                    ✅ Active
                @else
                    ⚠️ No Data
                @endif
            </div>
            <div class="stat-desc">
                @if(($total_visitors ?? 0) > 0)
                    Receiving visits
                @else
                    Install tracking script
                @endif
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Visits Over Time</h2>
                <div id="chartContainer">
                    @if(isset($chart_labels) && count($chart_labels) > 0)
                        <canvas id="simpleChart" width="400" height="200"></canvas>
                        <script>
                            const ctx = document.getElementById('simpleChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: {!! json_encode($chart_labels) !!},
                                    datasets: [
                                        {
                                            label: 'Total Visits',
                                            data: {!! json_encode($chart_total_values ?? []) !!},
                                            borderColor: 'rgb(59, 130, 246)',
                                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                            tension: 0.1,
                                            fill: false
                                        },
                                        {
                                            label: 'Unique Visitors',
                                            data: {!! json_encode($chart_unique_values ?? []) !!},
                                            borderColor: 'rgb(16, 185, 129)',
                                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                            tension: 0.1,
                                            fill: false
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        title: {
                                            display: true,
                                            text: 'Daily Visits Overview'
                                        },
                                        legend: {
                                            display: true,
                                            position: 'top'
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        </script>
                    @else
                        <div class="text-center p-8">
                            <div class="text-gray-500">No chart data available</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Page Views</h2>
                <div id="tableContainer">
                    @if(isset($table_rows) && count($table_rows) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Page URL</th>
                                        <th>Total Visits</th>
                                        <th>Unique Visitors</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalVisits = array_sum(array_column($table_rows, 'total_visits'));
                                    @endphp
                                    @foreach($table_rows as $row)
                                        @php
                                            $percentage = $totalVisits > 0 ? round(($row['total_visits'] / $totalVisits) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td class="font-mono text-sm">{{ $row['page_url'] }}</td>
                                            <td>
                                                <div class="badge badge-info">{{ $row['total_visits'] ?? 0 }}</div>
                                            </td>
                                            <td>
                                                <div class="badge badge-primary">{{ $row['unique_visits'] ?? 0 }}</div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-16 bg-base-300 rounded-full h-2">
                                                        <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="text-sm">{{ $percentage }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-8">
                            <div class="text-gray-500">No page data available</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Script Instructions -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">📊 How to Track Your Website</h2>
            <p class="mb-4">Add this script to your website to start tracking visitors:</p>
            <div class="mockup-code">
                <pre><code>&lt;script src="{{ $_SERVER['HTTP_HOST'] ?? 'your-domain.com' }}/api/tracking-script"&gt;&lt;/script&gt;</code></pre>
            </div>
        </div>
    </div>
</div>

<script>
function updatePeriod() {
    const days = document.getElementById('periodSelector').value;
    // Simple page reload with new period parameter
    window.location.href = `/dashboard?days=${days}`;
}
</script>
@endsection
