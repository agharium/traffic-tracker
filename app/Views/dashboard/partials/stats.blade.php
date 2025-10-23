<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-title">Total Unique Visitors</div>
        <div class="stat-value text-primary">{{ number_format($total_visitors) }}</div>
        <div class="stat-desc">Last {{ $period }} days</div>
    </div>
    <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-title">Most Popular Page</div>
        <div class="stat-value text-sm">
            @if(count($top_referrers) > 0)
                {{ $top_referrers[0]['referer'] ?? 'Direct traffic' }}
            @else
                No data yet
            @endif
        </div>
        <div class="stat-desc">Top traffic source</div>
    </div>
    <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-title">Tracking Status</div>
        <div class="stat-value text-success text-sm">
            @if($total_visitors > 0)
                ✅ Active
            @else
                ⚠️ No Data
            @endif
        </div>
        <div class="stat-desc">
            @if($total_visitors > 0)
                Receiving visits
            @else
                Install tracking script
            @endif
        </div>
    </div>
</div>
