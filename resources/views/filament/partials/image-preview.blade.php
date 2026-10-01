@if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL))
    <div class="ag-image-single"><img src="{{ $url }}" alt="" loading="lazy" onerror="this.style.display='none'"></div>
@endif
