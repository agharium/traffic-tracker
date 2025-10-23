<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>Page URL</th>
                <th>Unique Visitors</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = isset($rows) ? array_sum(array_column($rows, 'unique_visits')) : 0;
            @endphp
            @if(isset($rows) && count($rows) > 0)
                @foreach($rows as $r)
                    <tr>
                        <td>
                            <div class="font-mono text-sm">{{ $r['page_url'] }}</div>
                        </td>
                        <td>
                            <div class="badge badge-primary">{{ number_format($r['unique_visits']) }}</div>
                        </td>
                        <td>
                            @php
                                $percentage = $total > 0 ? round(($r['unique_visits'] / $total) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center gap-2">
                                <progress class="progress progress-primary w-20" value="{{ $percentage }}" max="100"></progress>
                                <span class="text-sm">{{ $percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center text-gray-500 py-8">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <div>No traffic data available</div>
                            <div class="text-xs">Install the tracking script to start collecting data</div>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
