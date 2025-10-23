@extends('layouts.base')

@section('content')
<div class="space-y-6">
    <!-- Header with website and period selector -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Traffic Dashboard</h1>
        <div class="flex gap-2">
            <!-- Website Selector -->
            <select id="websiteSelector" class="select select-bordered" onchange="updateWebsite()">
                <option value="all" {{ ($selected_website_id ?? 'all') == 'all' ? 'selected' : '' }}>All Websites</option>
                @if(isset($websites))
                    @foreach($websites as $website)
                        <option value="{{ $website->getId() }}" {{ ($selected_website_id ?? 'all') == $website->getId() ? 'selected' : '' }}>
                            {{ $website->getName() }} ({{ $website->getDomain() }})
                        </option>
                    @endforeach
                @endif
            </select>
            
            <!-- Period Selector -->
            <select id="periodSelector" class="select select-bordered" onchange="updatePeriod()">
                <option value="7" {{ ($selected_period ?? 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($selected_period ?? 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ ($selected_period ?? 30) == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
        </div>
    </div>

    <!-- Selected Website Info -->
    @if(isset($selected_website) && $selected_website)
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Showing data for: <strong>{{ $selected_website->getName() }}</strong> ({{ $selected_website->getDomain() }})</span>
        </div>
    @elseif(isset($websites) && count($websites) > 1)
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Showing combined data for <strong>{{ count($websites) }} websites</strong></span>
        </div>
    @endif

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
</div>

<script>
function updatePeriod() {
    const days = document.getElementById('periodSelector').value;
    const websiteId = document.getElementById('websiteSelector').value;
    // Preserve current website selection when updating period
    let url = `/dashboard?days=${days}`;
    if (websiteId !== 'all') {
        url += `&website_id=${websiteId}`;
    }
    window.location.href = url;
}

function updateWebsite() {
    const websiteId = document.getElementById('websiteSelector').value;
    const days = document.getElementById('periodSelector').value;
    // Preserve current period when updating website selection
    let url = `/dashboard?days=${days}`;
    if (websiteId !== 'all') {
        url += `&website_id=${websiteId}`;
    }
    window.location.href = url;
}
</script>
@endsection
