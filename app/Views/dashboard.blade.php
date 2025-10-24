@extends('layouts.base')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header with website and period selector -->
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
        <h1 class="text-2xl sm:text-3xl font-bold">Dashboard</h1>
        <div class="flex flex-col gap-2 sm:flex-row">
            <!-- Website Selector -->
            <select id="websiteSelector" class="select select-bordered select-sm sm:select-md w-full sm:w-auto min-h-[48px]" onchange="updateWebsite()">
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
            <select id="periodSelector" class="select select-bordered select-sm sm:select-md w-full sm:w-auto min-h-[48px]" onchange="updatePeriod()">
                <option value="1" {{ ($selected_period ?? 1) == 1 ? 'selected' : '' }}>Today</option>
                <option value="2" {{ ($selected_period ?? 1) == 2 ? 'selected' : '' }}>Yesterday</option>
                <option value="7" {{ ($selected_period ?? 1) == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($selected_period ?? 1) == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ ($selected_period ?? 1) == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
        </div>
    </div>

    <!-- Selected Website Info -->
    @if(isset($selected_website) && $selected_website)
        <div class="alert alert-info text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5 sm:w-6 sm:h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Showing data for: <strong>{{ $selected_website->getName() }}</strong> <span class="hidden sm:inline">({{ $selected_website->getDomain() }})</span></span>
        </div>
    @elseif(isset($websites) && count($websites) > 1)
        <div class="alert alert-info text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5 sm:w-6 sm:h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Showing combined data for <strong>{{ count($websites) }} websites</strong></span>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
        <div class="stat bg-base-100 rounded-lg shadow p-4 sm:p-6">
            <div class="stat-title text-xs sm:text-sm">Total Visits</div>
            <div class="stat-value text-info text-lg sm:text-2xl lg:text-3xl">{{ number_format($total_visits ?? 0) }}</div>
            <div class="stat-desc text-xs">
                @if($period == 1)
                    Today
                @elseif($period == 2) 
                    Yesterday
                @else
                    Last {{ $period }} days
                @endif
            </div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow p-4 sm:p-6">
            <div class="stat-title text-xs sm:text-sm">Unique Visitors</div>
            <div class="stat-value text-primary text-lg sm:text-2xl lg:text-3xl">{{ number_format($total_visitors ?? 0) }}</div>
            <div class="stat-desc text-xs">Distinct users</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow p-4 sm:p-6">
            <div class="stat-title text-xs sm:text-sm">Top Page</div>
            <div class="stat-value text-xs sm:text-sm truncate">
                @if(isset($top_pages) && count($top_pages) > 0)
                    {{ $top_pages[0]['page_url'] ?? 'No data yet' }}
                @else
                    No data yet
                @endif
            </div>
            <div class="stat-desc text-xs">
                @if(isset($top_pages) && count($top_pages) > 0)
                    {{ $top_pages[0]['total_visits'] ?? 0 }} visits
                @else
                    Most popular
                @endif
            </div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow p-4 sm:p-6">
            <div class="stat-title text-xs sm:text-sm">Status</div>
            <div class="stat-value text-xs sm:text-sm">
                @if(($total_visitors ?? 0) > 0)
                    <span class="text-success">✅ Active</span>
                @else
                    <span class="text-warning">⚠️ No Data</span>
                @endif
            </div>
            <div class="stat-desc text-xs">
                @if(($total_visitors ?? 0) > 0)
                    Receiving visits
                @else
                    Install tracking
                @endif
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Chart Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 sm:p-6">
                <h2 class="card-title text-lg sm:text-xl">Visits Over Time</h2>
                <div id="chartContainer">
                    @if(isset($chart_labels) && count($chart_labels) > 0)
                        <div class="w-full h-64 sm:h-80">
                            <canvas id="simpleChart" class="w-full h-full"></canvas>
                        </div>
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
                                    maintainAspectRatio: false,
                                    plugins: {
                                        title: {
                                            display: false
                                        },
                                        legend: {
                                            display: true,
                                            position: 'top',
                                            labels: {
                                                usePointStyle: true,
                                                padding: 20
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                font: {
                                                    size: window.innerWidth < 640 ? 10 : 12
                                                }
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                font: {
                                                    size: window.innerWidth < 640 ? 10 : 12
                                                },
                                                maxRotation: window.innerWidth < 640 ? 45 : 0
                                            }
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
            <div class="card-body p-4 sm:p-6">
                <h2 class="card-title text-lg sm:text-xl">Page Views</h2>
                <div id="tableContainer" class="max-h-80 overflow-y-auto">
                    @if(isset($table_rows) && count($table_rows) > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-xs sm:table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-xs">Page URL</th>
                                        <th class="text-xs text-center">Visits</th>
                                        <th class="text-xs text-center">Unique</th>
                                        <th class="text-xs text-center hidden sm:table-cell">%</th>
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
                                            <td class="font-mono text-xs truncate max-w-0 sm:max-w-none">
                                                <span class="block truncate">{{ $row['page_url'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="badge badge-info badge-xs sm:badge-sm">{{ $row['total_visits'] ?? 0 }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="badge badge-primary badge-xs sm:badge-sm">{{ $row['unique_visits'] ?? 0 }}</div>
                                            </td>
                                            <td class="hidden sm:table-cell">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-12 sm:w-16 bg-base-300 rounded-full h-1.5 sm:h-2">
                                                        <div class="bg-primary h-1.5 sm:h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs">{{ $percentage }}%</span>
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
