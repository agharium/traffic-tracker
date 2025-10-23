@if(isset($top_pages) && count($top_pages) > 0)
    {{ $top_pages[0]['page_url'] ?? 'No data yet' }}
@else
    No data yet
@endif
